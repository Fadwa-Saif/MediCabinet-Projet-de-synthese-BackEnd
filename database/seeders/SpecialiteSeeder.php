<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialites = [
            ['nom' => 'Médecine Générale', 'code' => 'MG', 'description' => 'Consultation générale et diagnostique initial'],
            ['nom' => 'Cardiologie', 'code' => 'CAR', 'description' => 'Maladies du cœur et du système cardiovasculaire'],
            ['nom' => 'Pédiatrie', 'code' => 'PED', 'description' => 'Médecine des enfants'],
            ['nom' => 'Dermatologie', 'code' => 'DER', 'description' => 'Maladies de la peau'],
            ['nom' => 'Orthopédie', 'code' => 'ORT', 'description' => 'Maladies des os et articulations'],
            ['nom' => 'ORL', 'code' => 'ORL', 'description' => 'Oto-rhino-laryngologie'],
            ['nom' => 'Gastroentérologie', 'code' => 'GAS', 'description' => 'Maladies du système digestif'],
            ['nom' => 'Pneumologie', 'code' => 'PNE', 'description' => 'Maladies des poumons'],
            ['nom' => 'Neurologie', 'code' => 'NEU', 'description' => 'Maladies du système nerveux'],
            ['nom' => 'Gynécologie', 'code' => 'GYN', 'description' => 'Santé des femmes et reproduction'],
            ['nom' => 'Ophtalmologie', 'code' => 'OPH', 'description' => 'Maladies des yeux'],
            ['nom' => 'Psychiatrie', 'code' => 'PSY', 'description' => 'Santé mentale'],
            ['nom' => 'Dentisterie', 'code' => 'DEN', 'description' => 'Santé dentaire'],
            ['nom' => 'Radiologie', 'code' => 'RAD', 'description' => 'Imagerie médicale'],
            ['nom' => 'Chirurgie', 'code' => 'CHI', 'description' => 'Interventions chirurgicales'],
        ];

        foreach ($specialites as $specialite) {
            DB::table('specialites')->insertOrIgnore([
                'nom' => $specialite['nom'],
                'code' => $specialite['code'],
                'description' => $specialite['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
