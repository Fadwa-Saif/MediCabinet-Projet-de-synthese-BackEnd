<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $medecinUserId = DB::table('users')->insertGetId([
            'nom' => 'Sabour',
            'prenom' => 'Rabia',
            'email' => 'admin@medicabinet.ma',
            'password' => Hash::make('password'),
            'telephone' => '0600000000',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admins')->insert([
            'user_id' => $medecinUserId,
            'role' => 'medecin',
            'matricule' => 'MED-001',
            'biographie' => 'Chef de projet MediCabinet',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secretaireUserId = DB::table('users')->insertGetId([
            'nom' => 'Chater',
            'prenom' => 'Basma',
            'email' => 'secretaire@medicabinet.ma',
            'password' => Hash::make('password'),
            'telephone' => '0611111111',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admins')->insert([
            'user_id' => $secretaireUserId,
            'role' => 'secretaire',
            'matricule' => 'SEC-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $patientUserId = DB::table('users')->insertGetId([
            'nom' => 'Saif',
            'prenom' => 'Fadwa',
            'email' => 'patient@medicabinet.ma',
            'password' => Hash::make('password'),
            'telephone' => '0622222222',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('patients')->insert([
            'user_id' => $patientUserId,
            'date_naissance' => '1995-06-15',
            'cin' => 'AB123456',
            'ville' => 'Casablanca',
            'groupe_sanguin' => 'A+',
            'date_creation_dossier' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
