<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\RendezVous;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $query = Consultation::with(['patient.user', 'admin.user'])
            ->orderByDesc('date');

        if ($user->isMedecin()) {
            $query->where('admin_id', $user->admin->id);
        } elseif ($user->isPatient()) {
            $query->where('patient_id', $user->patient->id);
        }

        $consultations = $query->paginate(20);

        return response()->json($consultations, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = auth('api')->user()->admin;

        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'rdv_id' => 'nullable|integer|exists:rendezvous,id',
            'date' => 'required|date',
            'symptomes' => 'nullable|string',
            'diagnostic' => 'nullable|string',
            'notes_medecin' => 'nullable|string',
        ]);

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

        return response()->json($consultation, 201);
    }

    public function show(Consultation $consultation): JsonResponse
    {
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
        $historiques = Consultation::with(['admin.user', 'ordonnances.medicaments'])
            ->where('patient_id', $patientId)
            ->orderByDesc('date')
            ->paginate(10);

        return response()->json($historiques, 200);
    }
}
