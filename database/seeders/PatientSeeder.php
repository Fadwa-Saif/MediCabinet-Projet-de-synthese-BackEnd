<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $patientIds = \Cache::get('patient_user_ids', []);

        $villes = ['Casablanca', 'Rabat', 'Fès', 'Marrakech', 'Tanger', 'Agadir', 'Meknes', 'Salé', 'Oujda', 'Kenitra'];
        $groupesSanguins = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        // Données de CIN marocains réalistes
        $cinNumbers = [
            'AB123456', 'CD234567', 'EF345678', 'GH456789', 'IJ567890',
            'KL678901', 'MN789012', 'OP890123', 'QR901234', 'ST012345',
            'UV123456', 'WX234567', 'YZ345678', 'AA456789', 'BB567890',
            'CC678901', 'DD789012', 'EE890123', 'FF901234', 'GG012345',
            'HH123456', 'II234567', 'JJ345678', 'KK456789', 'LL567890',
            'MM678901', 'NN789012', 'OO890123', 'PP901234', 'QQ012345',
        ];

        // Adresses réalistes à Casablanca
        $adressesCasablanca = [
            'Avenue Hassan II, Casablanca',
            'Boulevard Mohammed V, Casablanca',
            'Rue du Prince Moulay Abdellah, Casablanca',
            'Rue Allal Ben Abdellah, Casablanca',
            'Avenue des Forces Armées Royales, Casablanca',
        ];

        $adressesRabat = [
            'Avenue Mohammed VI, Rabat',
            'Rue Hassan II, Rabat',
            'Boulevard Abdelmoumen, Rabat',
        ];

        $adressesFes = [
            'Rue de la Kasbah, Fès',
            'Avenue Mohammed V, Fès',
            'Boulevard Dakhla, Fès',
        ];

        $adressesMarrakech = [
            'Rue Koutoubia, Marrakech',
            'Avenue Mohammed VI, Marrakech',
            'Rue Youssef Ibn Tachfine, Marrakech',
        ];

        $adresses = array_merge($adressesCasablanca, $adressesRabat, $adressesFes, $adressesMarrakech);

        $allergies = [
            null, 'Pénicilline', 'Ibuprofène', 'Aspirine', 'Sulfamides',
            'Codéine', 'Latex', 'Iode', 'Paracétamol', null
        ];

        $antecedents = [
            'Aucun antécédent notable',
            'Tension artérielle élevée',
            'Diabète type 2',
            'Asthme léger',
            'Cholestérol élevé',
            'Antécédent de migraines',
            'Apnée du sommeil',
            'Obésité',
            null,
            'Thyroïdite'
        ];

        $antecedentsFamiliaux = [
            'Parents en bonne santé',
            'Mère diabétique',
            'Père cardiaque',
            'Antécédent familial de cancer',
            'Mère hypertensive',
            'Grand-mère diabétique',
            'Père décédé d\'infarctus',
            null,
            'Antécédent de stroke familial',
            'Mère asthmatique'
        ];

        $traitements = [
            null, 'Antihypertenseur quotidien', 'Metformine 500mg x2', 'Aspirine 100mg',
            'Atorvastatine 20mg', 'Oméprazole 20mg', 'Vitamine D3', null,
            'Thyroxine', 'Ventoline au besoin'
        ];

        $patientProfileIds = [];
        foreach ($patientIds as $index => $userId) {
            $patientProfileIds[] = DB::table('patients')->insertGetId([
                'user_id' => $userId,
                'date_naissance' => Carbon::createFromDate(
                    rand(1950, 2015),
                    rand(1, 12),
                    rand(1, 28)
                )->toDateString(),
                'cin' => $cinNumbers[$index % count($cinNumbers)] . ($index > count($cinNumbers) ? $index : ''),
                'adresse' => $adresses[$index % count($adresses)],
                'ville' => $villes[$index % count($villes)],
                'groupe_sanguin' => $groupesSanguins[$index % count($groupesSanguins)],
                'antecedents' => $antecedents[$index % count($antecedents)],
                'antecedents_familiaux' => $antecedentsFamiliaux[$index % count($antecedentsFamiliaux)],
                'allergies' => $allergies[$index % count($allergies)],
                'poids_kg' => rand(50, 100) + (rand(0, 1) ? 0.5 : 0),
                'taille_cm' => rand(150, 190),
                'traitement_en_cours' => $traitements[$index % count($traitements)],
                'date_creation_dossier' => Carbon::now()->subMonths(rand(1, 24))->toDateString(),
                'dossier_updated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        \Cache::put('patient_ids', $patientProfileIds, 3600);
        \Cache::put('patient_profile_ids', $patientProfileIds, 3600);
    }
}
