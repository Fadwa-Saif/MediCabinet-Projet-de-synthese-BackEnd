<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabinetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Récupérer les IDs des médecins
        $medecinIds = \Cache::get('medecin_ids', []);

        $specialites = [
            'Médecine Générale', 'Cardiologie', 'Pédiatrie', 'Dermatologie', 'ORL',
            'Orthopédie', 'Gynécologie', 'Pneumologie', 'Neurologie', 'Gastroentérologie',
            'Ophtalmologie', 'Psychiatrie', 'Dentisterie', 'Radiologie', 'Chirurgie'
        ];

        // Adresses réalistes par ville
        $adressesCasablanca = [
            'Centre Commercial Anfa, Avenue Hassan II, Casablanca',
            'Place Mohammed V, Casablanca',
            'Rue Allal Ben Abdellah, Casablanca',
            'Boulevard Moulay Youssef, Casablanca',
            'Rue du Prince Moulay Abdellah, Casablanca',
        ];

        $adressesRabat = [
            'Avenue Mohammed VI, Rabat',
            'Boulevard Abdelmoumen, Rabat',
            'Rue Hassan II, Rabat',
        ];

        $adressesFes = [
            'Avenue Mohammed V, Fès',
            'Boulevard Dakhla, Fès',
            'Rue de la Kasbah, Fès',
        ];

        $adressesMarrakech = [
            'Rue Koutoubia, Marrakech',
            'Avenue Mohammed VI, Marrakech',
            'Rue Youssef Ibn Tachfine, Marrakech',
        ];

        $adressesTanger = [
            'Avenue Mohammed VI, Tanger',
            'Rue de Belgique, Tanger',
            'Place de la Méditerranée, Tanger',
        ];

        $villesAdresses = [
            'Casablanca' => $adressesCasablanca,
            'Rabat' => $adressesRabat,
            'Fès' => $adressesFes,
            'Marrakech' => $adressesMarrakech,
            'Tanger' => $adressesTanger,
        ];

        $villes = array_keys($villesAdresses);
        $nomsCabinets = [
            'Cabinet Médical %s',
            'Clinique %s',
            'Centre Médical %s',
            'Cabinet du Dr. %s',
            'Polyclinique %s',
        ];

        foreach ($medecinIds as $index => $medecinId) {
            $ville = $villes[$index % count($villes)];
            $adresse = $villesAdresses[$ville][$index % count($villesAdresses[$ville])];
            $specialite = $specialites[$index % count($specialites)];
            $nomTemplate = $nomsCabinets[$index % count($nomsCabinets)];
            $nom = sprintf($nomTemplate, $specialite);

            DB::table('cabinets')->insertOrIgnore([
                'nom' => $nom,
                'adresse' => $adresse,
                'ville' => $ville,
                'specialite' => $specialite,
                'docteur_id' => $medecinId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
