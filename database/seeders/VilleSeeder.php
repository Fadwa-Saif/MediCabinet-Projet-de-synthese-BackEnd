<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VilleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $villes = [
            ['nom' => 'Casablanca', 'code' => 'CAS', 'region' => 'Casablanca-Settat'],
            ['nom' => 'Rabat', 'code' => 'RBT', 'region' => 'Rabat-Salé-Kénitra'],
            ['nom' => 'Fès', 'code' => 'FES', 'region' => 'Fès-Meknès'],
            ['nom' => 'Marrakech', 'code' => 'MRK', 'region' => 'Marrakech-Safi'],
            ['nom' => 'Tanger', 'code' => 'TNG', 'region' => 'Tanger-Tétouan-Al Hoceïma'],
            ['nom' => 'Agadir', 'code' => 'AGA', 'region' => 'Souss-Massa'],
            ['nom' => 'Meknes', 'code' => 'MKN', 'region' => 'Fès-Meknès'],
            ['nom' => 'Salé', 'code' => 'SLE', 'region' => 'Rabat-Salé-Kénitra'],
            ['nom' => 'Oujda', 'code' => 'OJD', 'region' => 'Oriental'],
            ['nom' => 'Kenitra', 'code' => 'KNT', 'region' => 'Rabat-Salé-Kénitra'],
            ['nom' => 'Tetouan', 'code' => 'TTN', 'region' => 'Tanger-Tétouan-Al Hoceïma'],
            ['nom' => 'El Jadida', 'code' => 'EJD', 'region' => 'Casablanca-Settat'],
            ['nom' => 'Safi', 'code' => 'SFI', 'region' => 'Marrakech-Safi'],
            ['nom' => 'Laayoune', 'code' => 'LAY', 'region' => 'Laâyoune-Sakia El Hamra'],
            ['nom' => 'Azrou', 'code' => 'AZR', 'region' => 'Fès-Meknès'],
        ];

        foreach ($villes as $ville) {
            DB::table('villes')->insertOrIgnore([
                'nom' => $ville['nom'],
                'code' => $ville['code'],
                'region' => $ville['region'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
