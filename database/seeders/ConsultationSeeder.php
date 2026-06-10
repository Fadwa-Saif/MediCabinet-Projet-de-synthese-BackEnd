<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsultationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Récupérer les rendez-vous terminés pour créer des consultations
        $rendezVousTermines = DB::table('rendezvous')
            ->where('statut', 'termine')
            ->orderBy('id')
            ->take(20) // Au moins 20 consultations
            ->get();

        $adminIds = DB::table('admins')
            ->where('role', 'medecin')
            ->pluck('id')
            ->toArray();

        if (empty($adminIds)) {
            return;
        }

        $symptomesExamples = [
            'Fatigue générale, vertiges légers',
            'Toux sèche et persistante',
            'Maux de tête intenses',
            'Douleurs abdominales',
            'Problèmes de digestion',
            'Douleurs articulaires',
            'Fièvre modérée',
            'Mal de gorge',
            'Problèmes de sommeil',
            'Palpitations cardiaques',
            'Difficultés respiratoires',
            'Rash cutané',
            'Tremblements involontaires',
            'Problèmes d\'équilibre',
            'Sueurs nocturnes',
            'Perte d\'appétit',
            'Constipation',
            'Diarrhée',
            'Allergies saisonnières',
            'Fatigue chronique',
        ];

        $diagnosticsExamples = [
            'Syndrome viral léger',
            'Infection respiratoire',
            'Céphalée de tension',
            'Gastroentérite virale',
            'Dyspepsie',
            'Arthralgie sans lésion',
            'Fièvre d\'origine virale',
            'Pharyngite virale',
            'Insomnie transitoire',
            'Palpitations sans pathologie',
            'Asthme léger intermittent',
            'Dermatite simple',
            'Tremor fonctionnel',
            'Vertige positionnel',
            'Hyperhidrose nocturne',
            'Anorexie fonctionnelle',
            'Constipation fonctionnelle',
            'Diarrhée fonctionnelle',
            'Rhinite allergique',
            'Fatigue postvirale',
        ];

        $notesExamples = [
            'Repos recommandé, bien s\'hydrater',
            'Médicaments prescrits, contrôle dans 1 semaine',
            'Observation du patient, suivi ambulatoire',
            'Alimentation légère et hydrosoluble',
            'Éviter les efforts physiques intenses',
            'Traitement symptomatique',
            'Suivi régulier requis',
            'Bilan sanguin recommandé',
            'Contrôle dans 48h',
            'Hospitalisation non nécessaire',
            'Referral to specialist if needed',
            'Continue current treatment',
            'Éviter les facteurs déclenchants',
            'Prendre les médicaments régulièrement',
            'Faire des exercices de relaxation',
            'Marcher 30 minutes par jour',
            'Suivi ambulatoire',
            'Retour si aggravation',
            'Bon évolution clinique',
            'Patient information provided',
        ];

        foreach ($rendezVousTermines as $rdv) {
            $adminId = $adminIds[array_rand($adminIds)];
            
            DB::table('consultations')->insert([
                'rdv_id' => $rdv->id,
                'patient_id' => $rdv->patient_id,
                'admin_id' => $adminId,
                'date' => Carbon::parse($rdv->date_heure)->addMinutes(10),
                'symptomes' => $symptomesExamples[array_rand($symptomesExamples)],
                'diagnostic' => $diagnosticsExamples[array_rand($diagnosticsExamples)],
                'notes_medecin' => $notesExamples[array_rand($notesExamples)],
                'ordonnance' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
