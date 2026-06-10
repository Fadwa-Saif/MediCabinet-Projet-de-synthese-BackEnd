<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Désactiver les contraintes de clés étrangères pour le truncage
        Schema::disableForeignKeyConstraints();
        
        // Nettoyer les tables dans l'ordre inverse des dépendances
        foreach ([
            'notifications',
            'radiologies',
            'analyses',
            'prescription',
            'ordonnances',
            'consultations',
            'rendezvous',
            'disponibilites',
            'patient_medecin',
            'secretary_medecin',
            'patients',
            'cabinets',
            'admins',
            'users',
            'medicaments',
            'centres_radio_analyse',
        ] as $table) {
            if (DB::connection()->getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        
        // Réactiver les contraintes
        Schema::enableForeignKeyConstraints();

        // Exécuter les seeders dans l'ordre correct
        $this->call([
            VilleSeeder::class,
            MedicamentSeeder::class,
            MoroccanUserSeeder::class,
            AdminSeeder::class,
            PatientSeeder::class,
            CabinetSeeder::class,
            RendezvousSeeder::class,
            ConsultationSeeder::class,
            OrdonnanceSeeder::class,
            AnalyseSeeder::class,
        ]);
    }
}
