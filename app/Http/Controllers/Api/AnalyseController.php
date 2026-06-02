<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResolvesCabinetContext;
use App\Models\Analyse;
use App\Models\Consultation;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnalyseController extends Controller
{
    use ResolvesCabinetContext;

    // ── Patient: upload a new analysis ───────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultation_id'    => 'nullable|integer|exists:consultations,id',
            'type_analyse'       => 'nullable|string|max:50',
            'laboratoire'        => 'nullable|string|max:255',
            'date_analyse'       => 'nullable|date',
            'date_resultat'      => 'nullable|date',
            'commentaire_patient'=> 'nullable|string|max:2000',
            'fichier'            => 'required|file|mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx|max:10240',
        ]);

        $path = $request->file('fichier')->store('analyses', 'public');

        // Always link to the authenticated patient directly
        $user      = auth('api')->user();
        $patientId = $user->isPatient() ? optional($user->patient)->id : null;

        $analyse = Analyse::create([
            'group_id'            => null,
            'consultation_id'     => $validated['consultation_id'] ?? null,
            'patient_id'          => $patientId,
            'type_analyse'        => $validated['type_analyse'] ?? null,
            'laboratoire'         => $validated['laboratoire'] ?? null,
            'date_analyse'        => $validated['date_analyse'] ?? null,
            'date_resultat'       => $validated['date_resultat'] ?? null,
            'commentaire_patient' => $validated['commentaire_patient'] ?? null,
            'fichier'             => $path,
        ]);

        // Append a public URL so the frontend can display the file immediately
        return response()->json($this->withUrl($analyse), 201);
    }

    // ── Analyses for a specific consultation (used by doctor) ────────────

    public function index(int $consultationId): JsonResponse
    {
        $consultation = Consultation::with('admin.user')->findOrFail($consultationId);

        if ($response = $this->ensureConsultationAccess($consultation)) {
            return $response;
        }

        $analyses = Analyse::with('centre')
            ->where('consultation_id', $consultationId)
            ->orderByDesc('date_analyse')
            ->get()
            ->map(fn ($a) => $this->withUrl($a));

        return response()->json($analyses, 200);
    }

    // ── All analyses for the authenticated user ──────────────────────────
    //
    // Patient : sees everything they uploaded (via patient_id OR consultation)
    // Doctor  : sees analyses from all patients who have consulted them

    public function userAnalyses(): JsonResponse
    {
        $user  = auth('api')->user();
        $query = Analyse::with('centre');

        if ($user->isPatient()) {
            $patientId = optional($user->patient)->id;

            $query->where(function ($q) use ($patientId) {
                // Standalone uploads (the common case for this form)
                $q->where('patient_id', $patientId)
                  // OR attached to one of their consultations
                  ->orWhereHas('consultation', fn ($s) => $s->where('patient_id', $patientId));
            });

        } elseif ($user->isMedecin()) {
            $cabinetId = $this->tokenCabinetId();

            if (!$cabinetId) {
                return response()->json([], 200);
            }

            // Collect every patient who has ever consulted this doctor
            $patientIds = Consultation::whereHas('admin.user', function ($builder) use ($cabinetId) {
                    $builder->where('cabinet_id', $cabinetId);
                })
                ->pluck('patient_id')
                ->unique();

            $query->where(function ($q) use ($patientIds, $cabinetId) {
                // Standalone uploads by those patients
                $q->whereIn('patient_id', $patientIds)
                  // OR analyses attached to this doctor's consultations
                  ->orWhereHas('consultation.admin.user', fn ($s) => $s->where('cabinet_id', $cabinetId));
            });
        }

        $analyses = $query
            ->orderByDesc('date_analyse')
            ->get()
            ->map(fn ($a) => $this->withUrl($a));

        return response()->json($analyses, 200);
    }

    // ── Doctor: add a comment / result date ─────────────────────────────

    public function annoter(Request $request, Analyse $analyse): JsonResponse
    {
        if ($response = $this->ensureAnalyseAccess($analyse)) {
            return $response;
        }

        $validated = $request->validate([
            'commentaire_medecin' => 'required|string',
            'date_resultat'       => 'nullable|date',
        ]);

        $analyse->update($validated);

        return response()->json($this->withUrl($analyse), 200);
    }

    // ── Doctor: delete an analysis ───────────────────────────────────────

    public function destroy(Analyse $analyse): JsonResponse
    {
        if ($response = $this->ensureAnalyseAccess($analyse)) {
            return $response;
        }

        if (!empty($analyse->fichier)) {
            Storage::disk('public')->delete($analyse->fichier);
        }

        $analyse->delete();

        return response()->json(['message' => 'Analyse supprimée avec succès.'], 200);
    }
    // Médecin prescrit une analyse depuis une consultation (pas de fichier)
    public function prescrire(Request $request): JsonResponse
    {
        $cabinetId = $this->tokenCabinetId();

        $validated = $request->validate([
            'consultation_id' => 'required|integer|exists:consultations,id',
            'type_analyse'    => 'nullable|string|max:500',
            'category'        => 'nullable|string|max:50',
            'notes_medecin'   => 'nullable|string|max:1000',
            'date_analyse'    => 'nullable|date',
            'group_id'        => 'nullable|string',
            'notify_patient'  => 'nullable|boolean',
        ]);

        // Récupère le patient depuis la consultation
        $consultation = Consultation::with('patient.user')->findOrFail($validated['consultation_id']);

        if ($cabinetId && $consultation->admin?->user?->cabinet_id !== $cabinetId) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $groupId = $validated['group_id'] ?? (string) Str::uuid();
        $shouldNotifyPatient = $validated['notify_patient'] ?? true;

        $analyse = Analyse::create([
            'group_id'            => $groupId,
            'consultation_id'     => $validated['consultation_id'],
            'patient_id'          => $consultation->patient_id,
            'type_analyse'        => $validated['type_analyse'] ?? null,
            'category'            => $validated['category'] ?? null,
            'commentaire_medecin' => $validated['notes_medecin'] ?? null,
            'date_analyse'        => $validated['date_analyse'] ?? null,
            // fichier = null → statut "Prescrit"
        ]);

        // Notifier le patient qu'une nouvelle analyse a été prescrite
        $patientUserId = $consultation?->patient?->user_id;
        if ($patientUserId && $shouldNotifyPatient) {
            $doctor = auth('api')->user();
            $doctorName = $doctor
                ? trim(($doctor->prenom ?? '') . ' ' . ($doctor->nom ?? ''))
                : 'Votre médecin';
            $analyseLabel = $analyse->type_analyse ?: "Analyse #{$analyse->id}";

            Notification::create([
                'expediteur_id'   => $doctor?->id,
                'destinataire_id' => $patientUserId,
                'type'            => 'nouvelle_analyse',
                'canal'           => 'web',
                'titre'           => 'Nouvelle analyse prescrite',
                'contenu'         => json_encode([
                    'message'      => "{$doctorName} vous a prescrit {$analyseLabel}.",
                    'analyse_id'   => $analyse->id,
                    'group_id'     => $groupId,
                    'patient_id'   => $consultation->patient_id,
                    'redirect_to'  => "/patient/analyses/{$analyse->id}",
                ]),
                'lu'              => 0,
                'date_envoi'      => now(),
            ]);
        }

        return response()->json($analyse, 201);
    }

// Patient envoie le fichier pour une analyse prescrite
public function attachFichier(Request $request, Analyse $analyse): JsonResponse
{
    $user = auth('api')->user();
    if ($user?->isPatient()) {
        $patientId = optional($user->patient)->id;
        if ((int) $analyse->patient_id !== (int) $patientId) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }
    } elseif ($user?->isMedecin()) {
        if ($response = $this->ensureAnalyseAccess($analyse)) {
            return $response;
        }
    }

    $request->validate([
        'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx|max:10240',
    ]);

    $groupQuery = Analyse::query();
    if (!empty($analyse->group_id)) {
        $groupQuery->where('group_id', $analyse->group_id);
    } else {
        $groupQuery->where('id', $analyse->id);
    }

    $groupAnalyses = $groupQuery
        ->where('patient_id', $analyse->patient_id)
        ->get();

    $oldFiles = $groupAnalyses
        ->pluck('fichier')
        ->filter()
        ->unique()
        ->values()
        ->all();

    foreach ($oldFiles as $oldFile) {
        Storage::disk('public')->delete($oldFile);
    }

    $path = $request->file('fichier')->store('analyses', 'public');

    foreach ($groupAnalyses as $item) {
        $item->update([
            'fichier' => $path,
            'date_resultat' => now()->toDateString(),
        ]);
    }

    $analyse->refresh();

    // ── Notifier le médecin ──────────────────────────────────────
    if ($analyse->consultation_id) {
        $consultation = \App\Models\Consultation::with('admin.user')->find($analyse->consultation_id);
        if ($consultation?->admin?->user_id) {
            $patient = $analyse->patient;
            $patientName = $patient?->user
                ? trim($patient->user->prenom . ' ' . $patient->user->nom)
                : 'Un patient';

            \App\Models\Notification::create([
                'expediteur_id'   => $analyse->patient?->user_id,
                'destinataire_id' => $consultation->admin->user_id,
                'type'            => 'nouvelle_analyse',
                'canal'           => 'web',
                'titre'           => 'Résultat d\'analyse reçu',
                // JSON dans contenu pour stocker le lien de redirection
                'contenu'         => json_encode([
                    'message'    => "{$patientName} a envoyé le résultat de son analyse.",
                    'patient_id' => $consultation->patient_id,
                    'analyse_id' => $analyse->id,
                ]),
                'lu'              => 0,
                'date_envoi'      => now(),
            ]);
        }
    }

    return response()->json($this->withUrl($analyse), 200);
}

    // ── Serve analyse file directly (bypasses public storage symlink issues) ──
    /**
     * Serve an analysis file with proper authentication and authorization
     */
    public function fichier(Analyse $analyse)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        // Patient can only see their own analyses
        if ($user->isPatient()) {
            $patientId = optional($user->patient)->id;
            if ($analyse->patient_id !== $patientId) {
                return response()->json(['message' => 'Unauthorized - different patient'], 403);
            }
        }
        // Doctor can see analyses from their patients
        elseif ($user->isMedecin()) {
            // Doctor can access ANY analyse (for testing - can be restricted later)
            // TODO: Implement consultation-based access control if needed
        }

        if (!$analyse->fichier) {
            return response()->json(['message' => 'No file attached to analysis'], 404);
        }

        $path = storage_path('app/public/' . $analyse->fichier);
        
        if (!file_exists($path)) {
            \Log::error("File not found: " . $path);
            return response()->json(['message' => 'File not found on server', 'path' => $path], 404);
        }

        // Use response()->download() to serve the file
        return response()->download($path);
    }

    /** Append a public URL to every analyse so the frontend can render/download the file */
    private function withUrl(Analyse $analyse): Analyse
    {
        // Generate URL pointing to our new API endpoint instead of public storage
        $analyse->fichier_url = $analyse->fichier
            ? "/api/analyses/{$analyse->id}/fichier"
            : null;

        return $analyse;
    }

    private function ensureConsultationAccess(Consultation $consultation): ?JsonResponse
    {
        $user = auth('api')->user();

        if ($user?->isPatient()) {
            if ((int) optional($user->patient)->id !== (int) $consultation->patient_id) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            return null;
        }

        $cabinetId = $this->tokenCabinetId();
        if (!$cabinetId || !$consultation->admin?->user || (int) $consultation->admin->user->cabinet_id !== (int) $cabinetId) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return null;
    }

    private function ensureAnalyseAccess(Analyse $analyse): ?JsonResponse
    {
        $user = auth('api')->user();

        if ($user?->isPatient()) {
            if ((int) optional($user->patient)->id !== (int) $analyse->patient_id) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            return null;
        }

        $cabinetId = $this->tokenCabinetId();
        if (!$cabinetId) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($analyse->consultation?->admin?->user?->cabinet_id === $cabinetId) {
            return null;
        }

        if ($analyse->consultation_id) {
            $consultation = Consultation::with('admin.user')->find($analyse->consultation_id);
            if ($consultation?->admin?->user?->cabinet_id === $cabinetId) {
                return null;
            }
        }

        return response()->json(['message' => 'Non autorisé.'], 403);
    }
}
