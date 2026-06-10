<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnalyseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $patientIds = \Cache::get('patient_ids', []);
        $consultations = DB::table('consultations')
            ->orderBy('id')
            ->take(20)
            ->get();

        $typesAnalyses = [
            'NFS (Numération Formule Sanguine)',
            'Bilan lipidique',
            'Glycémie',
            'Biochimie générale',
            'Transaminases hépatiques',
            'Créatinine et urée',
            'Ionogramme sanguin',
            'Protéine C réactive',
            'Vitesse de sédimentation',
            'Coagulation (TP, INR)',
            'Bilan thyroïdien',
            'Prolactine',
            'Hormone de croissance',
            'Cortisol',
            'Électrolytes',
            'Albumine sérique',
            'Biliumbine',
            'Gamma GT',
            'Phosphatase alcaline',
            'Triglycérides',
        ];

        $laboratoires = [
            'Laboratoire Central Casablanca',
            'Laboratoire Ibn Sina Rabat',
            'Laboratoire Al Farabi Fès',
            'Laboratoire Marrakech Medical',
            'Laboratoire Tanger Health',
            'Laboratoire Agadir Plus',
            'Laboratoire Meknes Plus',
            'Laboratoire Oujda Expert',
            'Laboratoire Kenitra Health',
            'Laboratoire Salé Care',
        ];

        $resultatsNormaux = [
            'Résultats normaux',
            'Paramètres dans les normes',
            'Légèrement élevé',
            'Légèrement bas',
            'Valeurs normales pour l\'âge',
            'Résultats satisfaisants',
            'Aucune anomalie détectée',
            'À contrôler dans 3 mois',
            'Tendance normalisée',
            'En limite supérieure normale',
            'En limite inférieure normale',
            'Valeurs stables',
            'Amélioration notée',
            'Stabilité clinique',
        ];

        $commentairesPatients = [
            null,
            'Fatigue après les résultats',
            'Anxieux des résultats',
            'Soulagé des résultats normaux',
            'En attente des explications',
            'Préoccupé par quelques valeurs',
            'Satisfait des résultats',
            'Demande un rendez-vous de suivi',
            null,
            'Résultats conformes à mes attentes',
        ];

        $commentairesMedecins = [
            'À surveiller dans 3 mois',
            'Conseillé un régime équilibré',
            'Continuer le traitement actuel',
            'Pas de traitement nécessaire',
            'Résultats rassurants',
            'Valeurs stables, bon suivi',
            'À contrôler régulièrement',
            'Conseillé une activité physique',
            'Résultats sans surprise',
            'Patient bien contrôlé médicalement',
            'Recommandation de suivi',
            'Continuer le même traitement',
            'Les valeurs s\'améliorent',
            'Bon évolution clinique',
        ];

        $analysesCount = count($consultations);

        foreach ($consultations as $index => $consultation) {
            // Sélectionner un patient aléatoire
            $patientId = $patientIds[array_rand($patientIds)];

            // Dates: analyses passées ou récentes
            $daysBack = rand(1, 30);
            $dateAnalyse = Carbon::now()->subDays($daysBack);
            $dateResultat = $dateAnalyse->clone()->addDays(rand(2, 7));

            DB::table('analyses')->insert([
                'patient_id' => $patientId,
                'consultation_id' => $consultation->id,
                'centre_id' => null,
                'type_analyse' => $typesAnalyses[$index % count($typesAnalyses)],
                'laboratoire' => $laboratoires[$index % count($laboratoires)],
                'date_analyse' => $dateAnalyse->toDateString(),
                'date_resultat' => $dateResultat->toDateString(),
                'fichier' => null,
                'commentaire_patient' => $commentairesPatients[$index % count($commentairesPatients)],
                'commentaire_medecin' => $commentairesMedecins[$index % count($commentairesMedecins)],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
