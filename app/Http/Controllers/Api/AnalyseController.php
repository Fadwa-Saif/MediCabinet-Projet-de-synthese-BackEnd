<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnalyseController extends Controller
{
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
            'consultation_id'     => $validated['consultation_id'] ?? null,
            'patient_id'          => $patientId,
            'type_analyse'        => $validated['type_analyse'] ?? null,
            'laboratoire'         => $validated['laboratoire'] ?? null,
            'date_analyse'        => $validated['date_analyse'] ?? null,
            'date_resultat'       => $validated['date_resultat'] ?? null,
            'commentaire_patient' => $validated['commentaire_patient'] ?? null,
            'is_urgent'           => filter_var($request->input('is_urgent', false), FILTER_VALIDATE_BOOLEAN),
            'fichier'             => $path,
        ]);

        // Append a public URL so the frontend can display the file immediately
        $analyse->fichier_url = Storage::disk('public')->url($path);

        return response()->json($analyse, 201);
    }

    // ── Analyses for a specific consultation (used by doctor) ────────────

    public function index(int $consultationId): JsonResponse
    {
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
            $adminId = optional($user->admin)->id;

            // Collect every patient who has ever consulted this doctor
            $patientIds = Consultation::where('admin_id', $adminId)
                ->pluck('patient_id')
                ->unique();

            $query->where(function ($q) use ($patientIds, $adminId) {
                // Standalone uploads by those patients
                $q->whereIn('patient_id', $patientIds)
                  // OR analyses attached to this doctor's consultations
                  ->orWhereHas('consultation', fn ($s) => $s->where('admin_id', $adminId));
            });
        }

        $analyses = $query
            ->orderByDesc('is_urgent')   // urgent ones first
            ->orderByDesc('date_analyse')
            ->get()
            ->map(fn ($a) => $this->withUrl($a));

        return response()->json($analyses, 200);
    }

    // ── Doctor: add a comment / result date ─────────────────────────────

    public function annoter(Request $request, Analyse $analyse): JsonResponse
    {
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
        if (!empty($analyse->fichier)) {
            Storage::disk('public')->delete($analyse->fichier);
        }

        $analyse->delete();

        return response()->json(['message' => 'Analyse supprimée avec succès.'], 200);
    }

    // ── Helper ───────────────────────────────────────────────────────────

    /** Append a public URL to every analyse so the frontend can render/download the file */
    private function withUrl(Analyse $analyse): Analyse
    {
        $analyse->fichier_url = $analyse->fichier
            ? Storage::disk('public')->url($analyse->fichier)
            : null;

        return $analyse;
    }
}