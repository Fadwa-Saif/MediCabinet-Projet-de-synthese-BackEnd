<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Récupérer les IDs des médecins et secrétaires
        $medecinIds = \Cache::get('medecin_ids', []);
        $secretaireIds = \Cache::get('secretaire_ids', []);

        $specialites = [
            'Médecine Générale', 'Cardiologie', 'Pédiatrie', 'Dermatologie', 'ORL',
            'Orthopédie', 'Gynécologie', 'Pneumologie', 'Neurologie', 'Gastroentérologie',
            'Ophtalmologie', 'Psychiatrie', 'Dentisterie', 'Radiologie', 'Chirurgie'
        ];

        // Créer les admins (médecins)
        foreach ($medecinIds as $index => $userId) {
            DB::table('admins')->insertOrIgnore([
                'user_id' => $userId,
                'role' => 'medecin',
                'matricule' => 'MED-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'biographie' => 'Médecin spécialiste en ' . ($specialites[$index % count($specialites)] ?? 'Médecine Générale') . '. Expérience de plus de 10 ans dans le domaine médical.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Créer les admins (secrétaires)
        foreach ($secretaireIds as $index => $userId) {
            DB::table('admins')->insertOrIgnore([
                'user_id' => $userId,
                'role' => 'secretaire',
                'matricule' => 'SEC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'biographie' => 'Secrétaire médicale compétente avec expérience dans la gestion des dossiers patients.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
