<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MoroccanUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Médecins marocains
        $medecins = [
            ['nom' => 'Benali', 'prenom' => 'Ahmed', 'email' => 'benali.ahmed@medicabinet.ma', 'telephone' => '+212 661234567', 'specialite' => 'Médecine Générale'],
            ['nom' => 'El Amrani', 'prenom' => 'Sara', 'email' => 'sara.elamrani@medicabinet.ma', 'telephone' => '+212 662345678', 'specialite' => 'Cardiologie'],
            ['nom' => 'Bennani', 'prenom' => 'Fatima', 'email' => 'bennani.fatima@medicabinet.ma', 'telephone' => '+212 663456789', 'specialite' => 'Pédiatrie'],
            ['nom' => 'Tazi', 'prenom' => 'Mohamed', 'email' => 'tazi.mohamed@medicabinet.ma', 'telephone' => '+212 664567890', 'specialite' => 'Dermatologie'],
            ['nom' => 'Ouarti', 'prenom' => 'Yasmine', 'email' => 'ouarti.yasmine@medicabinet.ma', 'telephone' => '+212 665678901', 'specialite' => 'ORL'],
            ['nom' => 'Hajib', 'prenom' => 'Hassan', 'email' => 'hajib.hassan@medicabinet.ma', 'telephone' => '+212 666789012', 'specialite' => 'Orthopédie'],
            ['nom' => 'Mrani', 'prenom' => 'Leila', 'email' => 'mrani.leila@medicabinet.ma', 'telephone' => '+212 667890123', 'specialite' => 'Gynécologie'],
            ['nom' => 'Boukhchim', 'prenom' => 'Karim', 'email' => 'boukhchim.karim@medicabinet.ma', 'telephone' => '+212 668901234', 'specialite' => 'Pneumologie'],
            ['nom' => 'Chakkar', 'prenom' => 'Rachid', 'email' => 'chakkar.rachid@medicabinet.ma', 'telephone' => '+212 669012345', 'specialite' => 'Neurologie'],
            ['nom' => 'Elmir', 'prenom' => 'Nadia', 'email' => 'elmir.nadia@medicabinet.ma', 'telephone' => '+212 670123456', 'specialite' => 'Gastroentérologie'],
            ['nom' => 'Khider', 'prenom' => 'Ali', 'email' => 'khider.ali@medicabinet.ma', 'telephone' => '+212 671234567', 'specialite' => 'Ophtalmologie'],
            ['nom' => 'Razine', 'prenom' => 'Zainab', 'email' => 'razine.zainab@medicabinet.ma', 'telephone' => '+212 672345678', 'specialite' => 'Psychiatrie'],
            ['nom' => 'Mansouri', 'prenom' => 'Ibrahim', 'email' => 'mansouri.ibrahim@medicabinet.ma', 'telephone' => '+212 673456789', 'specialite' => 'Dentisterie'],
            ['nom' => 'Akherraz', 'prenom' => 'Hana', 'email' => 'akherraz.hana@medicabinet.ma', 'telephone' => '+212 674567890', 'specialite' => 'Radiologie'],
            ['nom' => 'Bedjaoui', 'prenom' => 'Tarek', 'email' => 'bedjaoui.tarek@medicabinet.ma', 'telephone' => '+212 675678901', 'specialite' => 'Chirurgie'],
        ];

        $medecinIds = [];
        foreach ($medecins as $medecin) {
            $medecinIds[] = DB::table('users')->insertGetId([
                'nom' => $medecin['nom'],
                'prenom' => $medecin['prenom'],
                'email' => $medecin['email'],
                'password' => Hash::make('Password123!'),
                'telephone' => $medecin['telephone'],
                'is_active' => 1,
                'photo_profil' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Secrétaires marocains
        $secretaires = [
            ['nom' => 'Chater', 'prenom' => 'Basma', 'email' => 'chater.basma@medicabinet.ma', 'telephone' => '+212 610000001'],
            ['nom' => 'Makoudi', 'prenom' => 'Amina', 'email' => 'makoudi.amina@medicabinet.ma', 'telephone' => '+212 610000002'],
            ['nom' => 'Saadi', 'prenom' => 'Hind', 'email' => 'saadi.hind@medicabinet.ma', 'telephone' => '+212 610000003'],
            ['nom' => 'Bensaid', 'prenom' => 'Samira', 'email' => 'bensaid.samira@medicabinet.ma', 'telephone' => '+212 610000004'],
            ['nom' => 'Kasmi', 'prenom' => 'Nadia', 'email' => 'kasmi.nadia@medicabinet.ma', 'telephone' => '+212 610000005'],
        ];

        $secretaireIds = [];
        foreach ($secretaires as $secretaire) {
            $secretaireIds[] = DB::table('users')->insertGetId([
                'nom' => $secretaire['nom'],
                'prenom' => $secretaire['prenom'],
                'email' => $secretaire['email'],
                'password' => Hash::make('Password123!'),
                'telephone' => $secretaire['telephone'],
                'is_active' => 1,
                'photo_profil' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Patients marocains (au moins 30)
        $patients = [
            ['nom' => 'El Fassi', 'prenom' => 'Houda', 'email' => 'houda.elfassi@patient.ma', 'telephone' => '+212 650000001'],
            ['nom' => 'Kassim', 'prenom' => 'Jamal', 'email' => 'jamal.kassim@patient.ma', 'telephone' => '+212 650000002'],
            ['nom' => 'Saif', 'prenom' => 'Fadwa', 'email' => 'fadwa.saif@patient.ma', 'telephone' => '+212 650000003'],
            ['nom' => 'Bennani', 'prenom' => 'Yassine', 'email' => 'yassine.bennani@patient.ma', 'telephone' => '+212 650000004'],
            ['nom' => 'Amrani', 'prenom' => 'Salma', 'email' => 'salma.amrani@patient.ma', 'telephone' => '+212 650000005'],
            ['nom' => 'Tazi', 'prenom' => 'Imane', 'email' => 'imane.tazi@patient.ma', 'telephone' => '+212 650000006'],
            ['nom' => 'Alaoui', 'prenom' => 'Mustafa', 'email' => 'mustafa.alaoui@patient.ma', 'telephone' => '+212 650000007'],
            ['nom' => 'Sefiani', 'prenom' => 'Zahra', 'email' => 'zahra.sefiani@patient.ma', 'telephone' => '+212 650000008'],
            ['nom' => 'Bencherif', 'prenom' => 'Karim', 'email' => 'karim.bencherif@patient.ma', 'telephone' => '+212 650000009'],
            ['nom' => 'Mounia', 'prenom' => 'Amal', 'email' => 'amal.mounia@patient.ma', 'telephone' => '+212 650000010'],
            ['nom' => 'Larbi', 'prenom' => 'Salim', 'email' => 'salim.larbi@patient.ma', 'telephone' => '+212 650000011'],
            ['nom' => 'Morchedi', 'prenom' => 'Nadia', 'email' => 'nadia.morchedi@patient.ma', 'telephone' => '+212 650000012'],
            ['nom' => 'Zouhair', 'prenom' => 'Laila', 'email' => 'laila.zouhair@patient.ma', 'telephone' => '+212 650000013'],
            ['nom' => 'Fakir', 'prenom' => 'Ahmed', 'email' => 'ahmed.fakir@patient.ma', 'telephone' => '+212 650000014'],
            ['nom' => 'Rais', 'prenom' => 'Hanane', 'email' => 'hanane.rais@patient.ma', 'telephone' => '+212 650000015'],
            ['nom' => 'Ouchani', 'prenom' => 'Soufiane', 'email' => 'soufiane.ouchani@patient.ma', 'telephone' => '+212 650000016'],
            ['nom' => 'Kacem', 'prenom' => 'Malika', 'email' => 'malika.kacem@patient.ma', 'telephone' => '+212 650000017'],
            ['nom' => 'Cheikh', 'prenom' => 'Amin', 'email' => 'amin.cheikh@patient.ma', 'telephone' => '+212 650000018'],
            ['nom' => 'Tahiri', 'prenom' => 'Siham', 'email' => 'siham.tahiri@patient.ma', 'telephone' => '+212 650000019'],
            ['nom' => 'Bouzidi', 'prenom' => 'Rachid', 'email' => 'rachid.bouzidi@patient.ma', 'telephone' => '+212 650000020'],
            ['nom' => 'Joundy', 'prenom' => 'Layla', 'email' => 'layla.joundy@patient.ma', 'telephone' => '+212 650000021'],
            ['nom' => 'Hanane', 'prenom' => 'Bilal', 'email' => 'bilal.hanane@patient.ma', 'telephone' => '+212 650000022'],
            ['nom' => 'Yousuf', 'prenom' => 'Hiba', 'email' => 'hiba.yousuf@patient.ma', 'telephone' => '+212 650000023'],
            ['nom' => 'Zaazaa', 'prenom' => 'Majid', 'email' => 'majid.zaazaa@patient.ma', 'telephone' => '+212 650000024'],
            ['nom' => 'Hamidou', 'prenom' => 'Rana', 'email' => 'rana.hamidou@patient.ma', 'telephone' => '+212 650000025'],
            ['nom' => 'Raissi', 'prenom' => 'Tariq', 'email' => 'tariq.raissi@patient.ma', 'telephone' => '+212 650000026'],
            ['nom' => 'Fiqih', 'prenom' => 'Mina', 'email' => 'mina.fiqih@patient.ma', 'telephone' => '+212 650000027'],
            ['nom' => 'Laasri', 'prenom' => 'Jamal', 'email' => 'jamal.laasri@patient.ma', 'telephone' => '+212 650000028'],
            ['nom' => 'Khalidi', 'prenom' => 'Rania', 'email' => 'rania.khalidi@patient.ma', 'telephone' => '+212 650000029'],
            ['nom' => 'Mahfoudh', 'prenom' => 'Samir', 'email' => 'samir.mahfoudh@patient.ma', 'telephone' => '+212 650000030'],
        ];

        $patientIds = [];
        foreach ($patients as $patient) {
            $patientIds[] = DB::table('users')->insertGetId([
                'nom' => $patient['nom'],
                'prenom' => $patient['prenom'],
                'email' => $patient['email'],
                'password' => Hash::make('Password123!'),
                'telephone' => $patient['telephone'],
                'is_active' => 1,
                'photo_profil' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Stocker les IDs pour utilisation dans les autres seeders
        \Cache::put('medecin_ids', $medecinIds, 3600);
        \Cache::put('secretaire_ids', $secretaireIds, 3600);
        \Cache::put('patient_user_ids', $patientIds, 3600);
    }
}
