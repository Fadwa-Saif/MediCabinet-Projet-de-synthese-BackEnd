<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RendezvousSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $patientIds = \Cache::get('patient_ids', []);
        $medecinIds = \Cache::get('medecin_ids', []);
        $adminIds = DB::table('admins')
            ->where('role', 'medecin')
            ->orderBy('id')
            ->take(count($medecinIds))
            ->pluck('id')
            ->toArray();

        if (empty($adminIds)) {
            return; // Pas de médecins créés
        }

        $motifs = [
            'Contrôle général',
            'Douleurs abdominales',
            'Suivi traitement',
            'Bilan de santé',
            'Lecture analyses',
            'Consultation post-urgence',
            'Suivi tension artérielle',
            'Toux persistante',
            'Problèmes de peau',
            'Douleurs articulation',
            'Mal de tête',
            'Suivi cardiaque',
            'Visite pédiatrique',
            'Consultation dermatologique',
            'Visite ORL',
            'Examen gynécologique',
            'Suivi diabète',
            'Problèmes respiratoires',
            'Consultation neurologique',
            'Problèmes digestifs',
        ];

        $statuts = ['en_attente', 'confirme', 'annule', 'termine'];
        $heures = [9, 10, 11, 12, 13, 14, 15, 16, 17];
        $minutes = [0, 30];

        // Générer au moins 40 rendez-vous
        $rendezVousCount = 40;
        $countRdv = 0;

        for ($i = 0; $i < $rendezVousCount; $i++) {
            $patientId = $patientIds[$i % count($patientIds)];
            $adminId = $adminIds[$i % count($adminIds)];
            
            // Répartir les dates: passées (30%), aujourd'hui (10%), futures (60%)
            $rand = rand(0, 100);
            if ($rand < 30) {
                // Dates passées
                $daysBack = rand(1, 60);
                $dateHeure = Carbon::now()->subDays($daysBack);
            } elseif ($rand < 40) {
                // Aujourd'hui
                $dateHeure = Carbon::today();
            } else {
                // Dates futures
                $daysFuture = rand(1, 90);
                $dateHeure = Carbon::now()->addDays($daysFuture);
            }

            // Ajouter l'heure
            $heure = $heures[array_rand($heures)];
            $minute = $minutes[array_rand($minutes)];
            $dateHeure = $dateHeure->setTime($heure, $minute, 0);

            // Statut: dates passées sont généralement terminées
            if ($dateHeure < Carbon::now()) {
                $statut = $statuts[array_rand($statuts, 1)];
                if ($statut === 'en_attente') {
                    $statut = 'termine'; // Force les rendez-vous passés à être terminés ou annulés
                }
            } else {
                $statut = ['en_attente', 'confirme', 'annule'][array_rand(['en_attente', 'confirme', 'annule'])];
            }

            DB::table('rendezvous')->insert([
                'patient_id' => $patientId,
                'admin_id' => $adminId,
                'date_heure' => $dateHeure,
                'duree_minutes' => 30,
                'motif' => $motifs[array_rand($motifs)],
                'statut' => $statut,
                'rappel_envoye' => ($statut === 'en_attente' && $dateHeure > Carbon::now()) ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $countRdv++;
        }
    }
}
