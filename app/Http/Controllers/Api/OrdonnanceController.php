<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ordonnance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdonnanceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $admin = auth('api')->user()->admin;

        $validated = $request->validate([
            'consultation_id' => 'required|integer|exists:consultations,id',
            'instructions' => 'nullable|string',
            'medicaments' => 'required|array|min:1',
            'medicaments.*.medicament_id' => 'required|integer|exists:medicaments,id',
            'medicaments.*.posologie' => 'required|string',
            'medicaments.*.observation' => 'nullable|string',
            'medicaments.*.quantite' => 'nullable|integer|min:1',
            'medicaments.*.duree_traitement' => 'nullable|string|max:50',
        ]);

        $ordonnance = Ordonnance::create([
            'consultation_id' => $validated['consultation_id'],
            'admin_id' => $admin->id,
            'date' => now()->toDateString(),
            'instructions' => $validated['instructions'] ?? null,
        ]);

        foreach ($validated['medicaments'] as $medicament) {
            $ordonnance->medicaments()->attach($medicament['medicament_id'], [
                'posologie' => $medicament['posologie'],
                'observation' => $medicament['observation'] ?? null,
                'quantite' => $medicament['quantite'] ?? 1,
                'duree_traitement' => $medicament['duree_traitement'] ?? null,
            ]);
        }

        $ordonnance->load('medicaments');

        return response()->json($ordonnance, 201);
    }

    public function show(Ordonnance $ordonnance): JsonResponse
    {
        $ordonnance->load([
            'consultation.patient.user',
            'admin.user',
            'medicaments',
        ]);

        return response()->json($ordonnance, 200);
    }

    public function userOrdonnances(): JsonResponse
    {
        $user = auth('api')->user();
        $query = Ordonnance::with(['medicaments', 'admin.user', 'consultation.patient.user']);

        if ($user->isPatient()) {
            $query->whereHas('consultation', function ($q) use ($user) {
                $q->where('patient_id', $user->patient->id);
            });
        } elseif ($user->isMedecin()) {
            $query->whereHas('consultation', function ($q) use ($user) {
                $q->where('admin_id', $user->admin->id);
            });
        }

        $ordonnances = $query->orderByDesc('date')->get();
        return response()->json($ordonnances, 200);
    }
}
