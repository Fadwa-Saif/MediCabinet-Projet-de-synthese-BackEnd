<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResolvesCabinetContext;
use App\Models\Admin;
use App\Models\Cabinet;
use App\Models\Disponibilite;
use App\Models\RendezVous;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    use ResolvesCabinetContext;

    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $query = RendezVous::with(['patient.user', 'admin.user']);

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 200));

        $allowedSortBy = ['created_at', 'date_heure'];
        $sortBy = $request->input('sort_by', 'created_at');
        if (!in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc'));
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        if ($request->has('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->has('date')) {
            $query->whereDate('date_heure', $request->input('date'));
        }

        if ($user->isMedecin()) {
            $cabinetId = $this->tokenCabinetId();
            if ($cabinetId) {
                $query->whereHas('admin.user', function ($builder) use ($cabinetId) {
                    $builder->where('cabinet_id', $cabinetId);
                });
            }
        } elseif ($user->isPatient()) {
            $query->where('patient_id', $user->patient->id);
        } elseif ($user->isSecretaire()) {
            $cabinetId = $this->tokenCabinetId();
            if ($cabinetId) {
                $query->whereHas('admin.user', function ($builder) use ($cabinetId) {
                    $builder->where('cabinet_id', $cabinetId);
                });
            }
        }

        $rendezvous = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($rendezvous, 200);
    }

    public function creneaux(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|integer|exists:admins,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($validated['date']);
        $dayOfWeek = $this->getDayOfWeekFrench($date->dayOfWeek);

        $disponibilite = Disponibilite::where('admin_id', $validated['admin_id'])
            ->where('jour_semaine', $dayOfWeek)
            ->first();

        if (!$disponibilite) {
            return response()->json(['creneaux' => []], 200);
        }

        $creneauxDisponibles = $disponibilite->getCreneaux();

        $creneauxPris = RendezVous::where('admin_id', $validated['admin_id'])
            ->whereDate('date_heure', $date)
            ->where('statut', '!=', 'annule')
            ->pluck('date_heure')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $creneaux = array_map(function (string $heure) use ($creneauxPris) {
            return [
                'heure' => $heure,
                'disponible' => !in_array($heure, $creneauxPris, true),
            ];
        }, $creneauxDisponibles);

        return response()->json(['creneaux' => $creneaux], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        $validated = $request->validate([
            'patient_id'    => 'nullable|integer|exists:patients,id',
            'admin_id'      => 'nullable|integer|exists:admins,id',
            'date_heure'    => 'required|date|after:now',
            'motif'         => 'nullable|string|max:255',
            'duree_minutes' => 'required|integer|min:1',
        ]);

        $adminId = $validated['admin_id'] ?? null;

        if (!$adminId) {
            $medecins = Admin::where('role', 'medecin')->orderBy('id')->get();

            if ($medecins->count() !== 1) {
                return response()->json([
                    'message' => 'Le médecin du cabinet doit être précisé.',
                ], 422);
            }

            $adminId = $medecins->first()->id;
        }

        $patient_id = null;

        if ($user->isPatient()) {
            $patient_id = $user->patient->id;
        } else {
            // Medecin or Secretaire must provide patient_id
            if (!$request->has('patient_id')) {
                return response()->json(['message' => 'Le champ patient_id est requis pour les administrateurs.'], 422);
            }
            $patient_id = $validated['patient_id'];
        }

        $conflictingRdv = RendezVous::where('admin_id', $adminId)
            ->where('date_heure', $validated['date_heure'])
            ->exists();

        if ($conflictingRdv) {
            return response()->json(['message' => 'Ce créneau est déjà réservé.'], 409);
        }

        $rendezvous = RendezVous::create([
            'patient_id' => $patient_id,
            'admin_id' => $adminId,
            'date_heure' => $validated['date_heure'],
            'motif' => $validated['motif'] ?? null,
            'duree_minutes' => $validated['duree_minutes'],
            'statut' => 'en_attente',
        ]);

        return response()->json($rendezvous, 201);
    }

    public function appointmentStore(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'cabinet_id' => 'required|integer|exists:cabinets,id',
            'patient_id' => 'nullable|integer|exists:patients,id',
            'date' => 'required|date',
            'heure' => 'required|date_format:H:i',
            'motif' => 'nullable|string|max:255',
        ]);

        $cabinet = Cabinet::with('doctor.admin')->findOrFail($validated['cabinet_id']);
        $adminId = $cabinet->doctor?->admin?->id;

        if (!$adminId) {
            return response()->json(['message' => 'Cabinet invalide.'], 422);
        }

        $patientId = $validated['patient_id'] ?? null;
        if ($user->isPatient()) {
            $patientId = optional($user->patient)->id;
        }

        if (!$patientId) {
            return response()->json(['message' => 'Le patient doit être précisé.'], 422);
        }

        $dateHeure = Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['heure']);

        $conflict = RendezVous::where('admin_id', $adminId)
            ->where('date_heure', $dateHeure)
            ->where('statut', '!=', 'annule')
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Ce créneau est déjà réservé.'], 409);
        }

        $rendezvous = RendezVous::create([
            'patient_id' => $patientId,
            'admin_id' => $adminId,
            'date_heure' => $dateHeure,
            'motif' => $validated['motif'] ?? null,
            'duree_minutes' => 30,
            'statut' => 'en_attente',
        ]);

        $rendezvous->load(['patient.user', 'admin.user']);

        return response()->json(['data' => $this->formatAppointment($rendezvous)], 201);
    }

    public function patientAppointments(): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user->isPatient()) {
            return response()->json([], 200);
        }

        $appointments = RendezVous::with(['patient.user', 'admin.user'])
            ->where('patient_id', $user->patient->id)
            ->orderByDesc('date_heure')
            ->get()
            ->map(fn (RendezVous $rendezVous) => $this->formatAppointment($rendezVous))
            ->values();

        return response()->json($appointments, 200);
    }

    public function update(Request $request, RendezVous $rendezvous): JsonResponse
    {
        if ($response = $this->ensureRdvAccess($rendezvous)) {
            return $response;
        }

        $validated = $request->validate([
            'date_heure'    => 'nullable|date|after:now',
            'motif'         => 'nullable|string|max:255',
            'duree_minutes' => 'nullable|integer|min:1',
            'statut'        => 'nullable|in:en_attente,confirme,annule,termine',
        ]);

        $oldStatut = $rendezvous->statut;

        $rendezvous->update($validated);

        // If status changed to 'confirme', send an in-app notification to the patient
        if (isset($validated['statut']) && $validated['statut'] === 'confirme' && $oldStatut !== 'confirme') {
            $patientUserId = $rendezvous->patient?->user?->id ?? null;
            $sender = auth('api')->user();

            if ($patientUserId) {
                Notification::create([
                    'expediteur_id'   => $sender?->id,
                    'destinataire_id' => $patientUserId,
                    'type'            => 'confirmation',
                    'canal'           => 'web',
                    'titre'           => 'Rendez-vous confirmé',
                    'contenu'         => 'Votre rendez-vous du ' . Carbon::parse($rendezvous->date_heure)->format('d/m/Y H:i') . ' a été confirmé.',
                    'lu'              => 0,
                    'date_envoi'      => Carbon::now(),
                ]);
            }
        }

        return response()->json($rendezvous, 200);
    }

    public function show(RendezVous $rendezvous): JsonResponse
    {
        if ($response = $this->ensureRdvAccess($rendezvous)) {
            return $response;
        }

        $rendezvous->load(['patient.user', 'admin.user', 'consultation']);

        return response()->json($rendezvous, 200);
    }

    public function annuler(RendezVous $rendezvous): JsonResponse
    {
        if ($response = $this->ensureRdvAccess($rendezvous)) {
            return $response;
        }

        if ($rendezvous->statut === 'annule' || $rendezvous->statut === 'termine') {
            return response()->json(['message' => 'Impossible d\'annuler ce rendez-vous.'], 422);
        }

        $rendezvous->annuler();

        return response()->json($rendezvous, 200);
    }

    // ← ADD THIS METHOD
    public function reprendre(RendezVous $rendezvous): JsonResponse
    {
        if ($response = $this->ensureRdvAccess($rendezvous)) {
            return $response;
        }

        if ($rendezvous->statut !== 'annule') {
            return response()->json(['message' => 'Seuls les rendez-vous annulés peuvent être repris.'], 422);
        }

        $rendezvous->update(['statut' => 'en_attente']);

        return response()->json([
            'message' => 'Rendez-vous repris avec succès',
            'data' => $rendezvous
        ], 200);
    }

    private function getDayOfWeekFrench(int $dayOfWeek): string
    {
        $days = [
            0 => 'Dim',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mer',
            4 => 'Jeu',
            5 => 'Ven',
            6 => 'Sam',
        ];

        return $days[$dayOfWeek] ?? 'Lun';
    }

    //modifier rendez vous par le patient (seulement si en_attente)
    public function updatePatient(Request $request, RendezVous $rendezvous): JsonResponse
{
    $user = auth('api')->user();
    
    // Authorize: patients can update their own RDV; secretaries may also update
    if ($user->isPatient()) {
        if ($user->patient->id !== $rendezvous->patient_id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
    } elseif (! $user->isSecretaire()) {
        return response()->json(['message' => 'Non autorisé.'], 403);
    }
    
    // Only allow editing if status is 'en_attente'
    if ($rendezvous->statut !== 'en_attente') {
        return response()->json(['message' => 'Seuls les rendez-vous en attente peuvent être modifiés.'], 422);
    }

    $validated = $request->validate([
        'date_heure'    => 'required|date|after:now',
        'motif'         => 'nullable|string|max:255',
        'duree_minutes' => 'required|integer|min:1',
    ]);

    // Check for conflicts (excluding current appointment)
    $conflictingRdv = RendezVous::where('admin_id', $rendezvous->admin_id)
        ->where('date_heure', $validated['date_heure'])
        ->where('id', '!=', $rendezvous->id)
        ->exists();

    if ($conflictingRdv) {
        return response()->json(['message' => 'Ce créneau est déjà réservé.'], 409);
    }

    $rendezvous->update($validated);

    return response()->json([
        'message' => 'Rendez-vous modifié avec succès',
        'data' => $rendezvous
    ], 200);
}

    private function ensureRdvAccess(RendezVous $rendezvous): ?JsonResponse
    {
        $user = auth('api')->user();

        if ($user?->isPatient()) {
            if ((int) optional($user->patient)->id !== (int) $rendezvous->patient_id) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            return null;
        }

        $cabinetId = $this->tokenCabinetId();
        if (!$cabinetId || !$rendezvous->admin?->user || (int) $rendezvous->admin->user->cabinet_id !== (int) $cabinetId) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return null;
    }

    private function formatAppointment(RendezVous $rendezvous): array
    {
        return [
            'id' => $rendezvous->id,
            'patient_id' => $rendezvous->patient_id,
            'cabinet_id' => $rendezvous->admin?->user?->cabinet_id,
            'date' => optional($rendezvous->date_heure)->toDateString(),
            'heure' => optional($rendezvous->date_heure)->format('H:i'),
            'motif' => $rendezvous->motif,
            'statut' => match ($rendezvous->statut) {
                'en_attente' => 'pending',
                'confirme' => 'confirmed',
                'annule' => 'cancelled',
                default => $rendezvous->statut,
            },
            'created_at' => $rendezvous->created_at,
            'patient' => $rendezvous->patient?->user,
            'doctor' => $rendezvous->admin?->user,
        ];
    }
}