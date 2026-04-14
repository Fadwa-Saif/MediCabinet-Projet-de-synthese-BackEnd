<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ordonnance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    /**
     * Create a prescription (pivot entry between ordonnance and medicament)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ordonnance_id' => 'required|integer|exists:ordonnances,id',
            'medicament_id' => 'required|integer|exists:medicaments,id',
            'posologie' => 'required|string',
            'observation' => 'nullable|string',
            'quantite' => 'nullable|integer|min:1',
            'duree_traitement' => 'nullable|string',
        ]);

        try {
            $ordonnance = Ordonnance::find($validated['ordonnance_id']);

            // Attach medicament to ordonnance with pivot data
            $ordonnance->medicaments()->attach(
                $validated['medicament_id'],
                [
                    'posologie' => $validated['posologie'],
                    'observation' => $validated['observation'] ?? null,
                    'quantite' => $validated['quantite'] ?? 1,
                    'duree_traitement' => $validated['duree_traitement'] ?? null,
                ]
            );

            return response()->json([
                'message' => 'Prescription créée avec succès',
                'ordonnance_id' => $validated['ordonnance_id'],
                'medicament_id' => $validated['medicament_id'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la prescription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
