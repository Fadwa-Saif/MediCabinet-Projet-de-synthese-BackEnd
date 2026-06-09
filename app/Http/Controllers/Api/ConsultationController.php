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
            // Existing fields (keep intact)
            'patient_id' => 'required|integer|exists:patients,id',
            'rdv_id' => 'nullable|integer|exists:rendezvous,id',
            'date' => 'required|date',
            'symptomes' => 'nullable|string',
            'diagnostic' => 'nullable|string',
            'notes_medecin' => 'nullable|string',

            // EN-TÊTE
            'type_consultation' => 'nullable|string|max:100',

            // SECTION S: SUBJECTIF
            'motif' => 'nullable|string|max:255',
            'histoire_maladie' => 'nullable|string',
            'douleur_presente' => 'nullable|boolean',
            'douleur_localisation' => 'nullable|string|max:255',
            'douleur_type' => 'nullable|string|max:100',
            'douleur_intensite_eva' => 'nullable|integer|min:0|max:10',
            'symptomes_associes' => 'nullable|array',
            'symptomes_associes.*' => 'nullable|string',

            // SECTION O: OBJECTIF — SIGNES VITAUX
            'tension_arterielle_sys' => 'nullable|integer|min:0|max:300',
            'tension_arterielle_dia' => 'nullable|integer|min:0|max:300',
            'frequence_cardiaque' => 'nullable|integer|min:0|max:300',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'spo2' => 'nullable|integer|min:0|max:100',
            'frequence_respiratoire' => 'nullable|integer|min:0|max:100',
            'poids' => 'nullable|numeric|min:0|max:500',
            'taille' => 'nullable|integer|min:0|max:300',
            'imc' => 'nullable|numeric|min:0|max:100',

            // SECTION O: OBJECTIF — EXAMEN CLINIQUE
            'examen_etat_general' => 'nullable|string',
            'examen_cardiovasculaire' => 'nullable|string',
            'examen_respiratoire' => 'nullable|string',
            'examen_abdomen' => 'nullable|string',
            'examen_neurologique' => 'nullable|string',
            'examen_osteomusculaire' => 'nullable|string',
            'examen_peau_muqueuses' => 'nullable|string',
            'examen_autres' => 'nullable|string',

            // SECTION A: ÉVALUATION
            'diagnostic_code_cim10' => 'nullable|string|max:50',
            'diagnostics_secondaires' => 'nullable|array',
            'diagnostics_secondaires.*.libelle' => 'nullable|string',
            'diagnostics_secondaires.*.code_cim10' => 'nullable|string|max:50',
            'raisonnement_clinique' => 'nullable|string',

            // SECTION P: PLAN THÉRAPEUTIQUE
            'refere_specialiste' => 'nullable|boolean',
            'refere_specialite' => 'nullable|string|max:100',
            'refere_urgence' => 'nullable|string|max:50',
            'hospitalisation' => 'nullable|boolean',
            'hospitalisation_service' => 'nullable|string|max:100',
            'arret_travail' => 'nullable|boolean',
            'arret_travail_duree' => 'nullable|integer|min:1|max:365',
            'arret_travail_date_debut' => 'nullable|date',
            'prochain_rdv' => 'nullable|date',
            'instructions_patient' => 'nullable|string',
        ]);
        $validated['admin_id'] = auth()->user()->admin->id; // ← injected server-side

        $consultation = Consultation::create([
            // Existing fields (keep intact)
            'rdv_id' => $validated['rdv_id'] ?? null,
            'patient_id' => $validated['patient_id'],
            'admin_id' => $admin->id,
            'date' => $validated['date'],
            'symptomes' => $validated['symptomes'] ?? null,
            'diagnostic' => $validated['diagnostic'] ?? null,
            'notes_medecin' => $validated['notes_medecin'] ?? null,

            // EN-TÊTE
            'type_consultation' => $validated['type_consultation'] ?? null,

            // SECTION S: SUBJECTIF
            'motif' => $validated['motif'] ?? null,
            'histoire_maladie' => $validated['histoire_maladie'] ?? null,
            'douleur_presente' => $validated['douleur_presente'] ?? null,
            'douleur_localisation' => $validated['douleur_localisation'] ?? null,
            'douleur_type' => $validated['douleur_type'] ?? null,
            'douleur_intensite_eva' => $validated['douleur_intensite_eva'] ?? null,
            'symptomes_associes' => $validated['symptomes_associes'] ?? null,

            // SECTION O: OBJECTIF — SIGNES VITAUX
            'tension_arterielle_sys' => $validated['tension_arterielle_sys'] ?? null,
            'tension_arterielle_dia' => $validated['tension_arterielle_dia'] ?? null,
            'frequence_cardiaque' => $validated['frequence_cardiaque'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'spo2' => $validated['spo2'] ?? null,
            'frequence_respiratoire' => $validated['frequence_respiratoire'] ?? null,
            'poids' => $validated['poids'] ?? null,
            'taille' => $validated['taille'] ?? null,
            'imc' => $validated['imc'] ?? null,

            // SECTION O: OBJECTIF — EXAMEN CLINIQUE
            'examen_etat_general' => $validated['examen_etat_general'] ?? null,
            'examen_cardiovasculaire' => $validated['examen_cardiovasculaire'] ?? null,
            'examen_respiratoire' => $validated['examen_respiratoire'] ?? null,
            'examen_abdomen' => $validated['examen_abdomen'] ?? null,
            'examen_neurologique' => $validated['examen_neurologique'] ?? null,
            'examen_osteomusculaire' => $validated['examen_osteomusculaire'] ?? null,
            'examen_peau_muqueuses' => $validated['examen_peau_muqueuses'] ?? null,
            'examen_autres' => $validated['examen_autres'] ?? null,

            // SECTION A: ÉVALUATION
            'diagnostic_code_cim10' => $validated['diagnostic_code_cim10'] ?? null,
            'diagnostics_secondaires' => $validated['diagnostics_secondaires'] ?? null,
            'raisonnement_clinique' => $validated['raisonnement_clinique'] ?? null,

            // SECTION P: PLAN THÉRAPEUTIQUE
            'refere_specialiste' => $validated['refere_specialiste'] ?? null,
            'refere_specialite' => $validated['refere_specialite'] ?? null,
            'refere_urgence' => $validated['refere_urgence'] ?? null,
            'hospitalisation' => $validated['hospitalisation'] ?? null,
            'hospitalisation_service' => $validated['hospitalisation_service'] ?? null,
            'arret_travail' => $validated['arret_travail'] ?? null,
            'arret_travail_duree' => $validated['arret_travail_duree'] ?? null,
            'arret_travail_date_debut' => $validated['arret_travail_date_debut'] ?? null,
            'prochain_rdv' => $validated['prochain_rdv'] ?? null,
            'instructions_patient' => $validated['instructions_patient'] ?? null,
        ]);

        if (!empty($validated['rdv_id'])) {
            RendezVous::find($validated['rdv_id'])?->terminer();
        }

        $consultation->load(['patient.user', 'admin.user']);

        return response()->json(['data' => $consultation], 201);
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
            // Existing fields (keep intact)
            'symptomes' => 'nullable|string',
            'diagnostic' => 'nullable|string',
            'notes_medecin' => 'nullable|string',

            // EN-TÊTE
            'type_consultation' => 'nullable|string|max:100',

            // SECTION S: SUBJECTIF
            'motif' => 'nullable|string|max:255',
            'histoire_maladie' => 'nullable|string',
            'douleur_presente' => 'nullable|boolean',
            'douleur_localisation' => 'nullable|string|max:255',
            'douleur_type' => 'nullable|string|max:100',
            'douleur_intensite_eva' => 'nullable|integer|min:0|max:10',
            'symptomes_associes' => 'nullable|array',
            'symptomes_associes.*' => 'nullable|string',

            // SECTION O: OBJECTIF — SIGNES VITAUX
            'tension_arterielle_sys' => 'nullable|integer|min:0|max:300',
            'tension_arterielle_dia' => 'nullable|integer|min:0|max:300',
            'frequence_cardiaque' => 'nullable|integer|min:0|max:300',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'spo2' => 'nullable|integer|min:0|max:100',
            'frequence_respiratoire' => 'nullable|integer|min:0|max:100',
            'poids' => 'nullable|numeric|min:0|max:500',
            'taille' => 'nullable|integer|min:0|max:300',
            'imc' => 'nullable|numeric|min:0|max:100',

            // SECTION O: OBJECTIF — EXAMEN CLINIQUE
            'examen_etat_general' => 'nullable|string',
            'examen_cardiovasculaire' => 'nullable|string',
            'examen_respiratoire' => 'nullable|string',
            'examen_abdomen' => 'nullable|string',
            'examen_neurologique' => 'nullable|string',
            'examen_osteomusculaire' => 'nullable|string',
            'examen_peau_muqueuses' => 'nullable|string',
            'examen_autres' => 'nullable|string',

            // SECTION A: ÉVALUATION
            'diagnostic_code_cim10' => 'nullable|string|max:50',
            'diagnostics_secondaires' => 'nullable|array',
            'diagnostics_secondaires.*.libelle' => 'nullable|string',
            'diagnostics_secondaires.*.code_cim10' => 'nullable|string|max:50',
            'raisonnement_clinique' => 'nullable|string',

            // SECTION P: PLAN THÉRAPEUTIQUE
            'refere_specialiste' => 'nullable|boolean',
            'refere_specialite' => 'nullable|string|max:100',
            'refere_urgence' => 'nullable|string|max:50',
            'hospitalisation' => 'nullable|boolean',
            'hospitalisation_service' => 'nullable|string|max:100',
            'arret_travail' => 'nullable|boolean',
            'arret_travail_duree' => 'nullable|integer|min:1|max:365',
            'arret_travail_date_debut' => 'nullable|date',
            'prochain_rdv' => 'nullable|date',
            'instructions_patient' => 'nullable|string',
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
