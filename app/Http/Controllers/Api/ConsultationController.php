<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResolvesCabinetContext;
use App\Models\Consultation;
use App\Models\RendezVous;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    use ResolvesCabinetContext;

    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $query = Consultation::with(['patient.user', 'admin.user'])
            ->orderByDesc('date');

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

        $consultations = $query->paginate(20);

        return response()->json($consultations, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = auth('api')->user()->admin;
        $cabinetId = $this->tokenCabinetId();

        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'rdv_id' => 'nullable|integer|exists:rendezvous,id',
            'date' => 'required|date',
            'symptomes' => 'nullable|string',
            'diagnostic' => 'nullable|string',
            'notes_medecin' => 'nullable|string',
        ]);
        $validated['admin_id'] = auth()->user()->admin->id; // ← injected server-side

        if ($cabinetId) {
            $patientInCabinet = $this->patientBelongsToCabinet($validated['patient_id'], $cabinetId);
            if (!$patientInCabinet) {
                return response()->json(['message' => 'Ce patient n\'est pas rattaché à votre cabinet.'], 403);
            }
        }


        $consultation = Consultation::create([
            'rdv_id' => $validated['rdv_id'] ?? null,
            'patient_id' => $validated['patient_id'],
            'admin_id' => $admin->id,
            'date' => $validated['date'],
            'symptomes' => $validated['symptomes'] ?? null,
            'diagnostic' => $validated['diagnostic'] ?? null,
            'notes_medecin' => $validated['notes_medecin'] ?? null,
        ]);

        if (!empty($validated['rdv_id'])) {
            RendezVous::find($validated['rdv_id'])?->terminer();
        }

        $consultation->load(['patient.user', 'admin.user']);

        return response()->json(['data' => $consultation], 201);
    }

    public function show(Consultation $consultation): JsonResponse
    {
        if ($response = $this->ensureConsultationAccess($consultation)) {
            return $response;
        }

        $consultation->load([
            'patient.user',
            'admin.user',
            'ordonnances.medicaments',
            'analyses',
            'radiologies',
        ]);

        return response()->json($consultation, 200);
    }

    public function update(Request $request, Consultation $consultation): JsonResponse
    {
        if ($response = $this->ensureConsultationAccess($consultation)) {
            return $response;
        }

        $validated = $request->validate([
            'symptomes' => 'nullable|string',
            'diagnostic' => 'nullable|string',
            'notes_medecin' => 'nullable|string',
        ]);

        $consultation->update($validated);

        return response()->json($consultation, 200);
    }

    public function historique(Request $request, int $patientId): JsonResponse
    {
        $cabinetId = $this->tokenCabinetId();

        if ($cabinetId && !$this->patientBelongsToCabinet($patientId, $cabinetId)) {
            return response()->json(['message' => 'Ce patient n\'est pas rattaché à votre cabinet.'], 403);
        }

        $historiques = Consultation::with(['admin.user', 'ordonnances.medicaments'])
            ->where('patient_id', $patientId)
            ->when($cabinetId, function ($query) use ($cabinetId) {
                $query->whereHas('admin.user', function ($builder) use ($cabinetId) {
                    $builder->where('cabinet_id', $cabinetId);
                });
            })
            ->orderByDesc('date')
            ->paginate(10);

        return response()->json($historiques, 200);
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
}
