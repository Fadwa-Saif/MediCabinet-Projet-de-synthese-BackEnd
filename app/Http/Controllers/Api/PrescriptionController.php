<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ordonnance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class PrescriptionController extends Controller
{
    /**
     * Get all prescriptions for user's ordonnances
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth('api')->user();
            
            if ($user->isPatient()) {
                // Get prescriptions from patient's consultations
                $patientId = optional($user->patient)->id;
                
                $prescriptions = \DB::table('prescription')
                    ->join('ordonnances', 'prescription.ordonnance_id', '=', 'ordonnances.id')
                    ->join('consultations', 'ordonnances.consultation_id', '=', 'consultations.id')
                    ->join('medicaments', 'prescription.medicament_id', '=', 'medicaments.id')
                    ->where('consultations.patient_id', $patientId)
                    ->select(
                        'prescription.medicament_id',
                        'prescription.ordonnance_id',
                        'prescription.posologie',
                        'prescription.observation',
                        'prescription.quantite',
                        'prescription.duree_traitement',
                        'medicaments.nom as medicament_nom',
                        'medicaments.forme as medicament_forme'
                    )
                    ->get();
                
                return response()->json(['data' => $prescriptions], 200);
            } elseif ($user->isMedecin()) {
                // Get prescriptions from doctor's ordonnances
                $adminId = optional($user->admin)->id;
                
                $prescriptions = \DB::table('prescription')
                    ->join('ordonnances', 'prescription.ordonnance_id', '=', 'ordonnances.id')
                    ->join('consultations', 'ordonnances.consultation_id', '=', 'consultations.id')
                    ->join('medicaments', 'prescription.medicament_id', '=', 'medicaments.id')
                    ->where('consultations.admin_id', $adminId)
                    ->select(
                        'prescription.medicament_id',
                        'prescription.ordonnance_id',
                        'prescription.posologie',
                        'prescription.observation',
                        'prescription.quantite',
                        'prescription.duree_traitement',
                        'medicaments.nom as medicament_nom',
                        'medicaments.forme as medicament_forme'
                    )
                    ->get();
                
                return response()->json(['data' => $prescriptions], 200);
            }
            
            return response()->json(['data' => []], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des prescriptions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
