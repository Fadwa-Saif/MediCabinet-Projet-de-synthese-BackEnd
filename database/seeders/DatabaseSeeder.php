<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        Schema::disableForeignKeyConstraints();
        foreach ([
            'notifications',
            'radiologies',
            'analyses',
            'prescription',
            'medicaments',
            'ordonnances',
            'consultations',
            'rendezvous',
            'disponibilites',
            'centres_radio_analyse',
            'patients',
            'admins',
            'users',
        ] as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();

        $medecinUserId = DB::table('users')->insertGetId([
            'nom' => 'Sabour',
            'prenom' => 'Rabia',
            'email' => 'admin@medicabinet.ma',
            'password' => Hash::make('password'),
            'telephone' => '0600000000',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $secretaireUserId = DB::table('users')->insertGetId([
            'nom' => 'Chater',
            'prenom' => 'Basma',
            'email' => 'secretaire@medicabinet.ma',
            'password' => Hash::make('password'),
            'telephone' => '0611111111',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $patientUserIds = [];
        $patientUsers = [
            ['nom' => 'Saif', 'prenom' => 'Fadwa', 'email' => 'patient@medicabinet.ma', 'telephone' => '0622222222'],
            ['nom' => 'Bennani', 'prenom' => 'Yassine', 'email' => 'yassine.patient@medicabinet.ma', 'telephone' => '0623333333'],
            ['nom' => 'Amrani', 'prenom' => 'Salma', 'email' => 'salma.patient@medicabinet.ma', 'telephone' => '0624444444'],
            ['nom' => 'Tazi', 'prenom' => 'Imane', 'email' => 'imane.patient@medicabinet.ma', 'telephone' => '0625555555'],
        ];

        foreach ($patientUsers as $index => $userData) {
            $patientUserIds[] = DB::table('users')->insertGetId([
                'nom' => $userData['nom'],
                'prenom' => $userData['prenom'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'telephone' => $userData['telephone'],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $medecinAdminId = DB::table('admins')->insertGetId([
            'user_id' => $medecinUserId,
            'role' => 'medecin',
            'matricule' => 'MED-001',
            'biographie' => 'Medecin generaliste. Donnees de test pour MediCabinet.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $secretaireAdminId = DB::table('admins')->insertGetId([
            'user_id' => $secretaireUserId,
            'role' => 'secretaire',
            'matricule' => 'SEC-001',
            'biographie' => 'Secretaire medicale. Donnees de test pour MediCabinet.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $patientIds = [];
        $patientProfiles = [
            ['date_naissance' => '1995-06-15', 'cin' => 'AB123456', 'ville' => 'Casablanca', 'groupe' => 'A+'],
            ['date_naissance' => '1990-03-02', 'cin' => 'CD234567', 'ville' => 'Rabat', 'groupe' => 'O+'],
            ['date_naissance' => '1988-11-21', 'cin' => 'EF345678', 'ville' => 'Marrakech', 'groupe' => 'B+'],
            ['date_naissance' => '2001-08-09', 'cin' => 'GH456789', 'ville' => 'Fes', 'groupe' => 'A-'],
        ];

        foreach ($patientUserIds as $i => $userId) {
            $profile = $patientProfiles[$i];
            $patientIds[] = DB::table('patients')->insertGetId([
                'user_id' => $userId,
                'date_naissance' => $profile['date_naissance'],
                'cin' => $profile['cin'],
                'adresse' => 'Adresse test ' . ($i + 1),
                'ville' => $profile['ville'],
                'groupe_sanguin' => $profile['groupe'],
                'antecedents' => 'Aucun antecedent critique.',
                'antecedents_familiaux' => 'RAS',
                'allergies' => $i === 2 ? 'Penicilline' : null,
                'poids_kg' => 60 + ($i * 4),
                'taille_cm' => 165 + ($i * 2),
                'traitement_en_cours' => $i === 1 ? 'Vitamine D' : null,
                'date_creation_dossier' => Carbon::now()->subMonths(6)->toDateString(),
                'dossier_updated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $jours = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven'];
        foreach ($jours as $jour) {
            DB::table('disponibilites')->insert([
                'admin_id' => $medecinAdminId,
                'jour_semaine' => $jour,
                'heure_debut' => '09:00:00',
                'heure_fin' => '13:00:00',
                'duree_min' => 30,
                'est_disponible' => 1,
                'date_exception' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('disponibilites')->insert([
                'admin_id' => $medecinAdminId,
                'jour_semaine' => $jour,
                'heure_debut' => '14:00:00',
                'heure_fin' => '18:00:00',
                'duree_min' => 30,
                'est_disponible' => 1,
                'date_exception' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $rdvIds = [];
        $rdvPayload = [
            ['patient_id' => $patientIds[0], 'date_heure' => Carbon::today()->setTime(9, 0), 'statut' => 'en_attente', 'motif' => 'Controle general'],
            ['patient_id' => $patientIds[1], 'date_heure' => Carbon::today()->setTime(10, 30), 'statut' => 'confirme', 'motif' => 'Douleurs abdominales'],
            ['patient_id' => $patientIds[2], 'date_heure' => Carbon::today()->setTime(15, 0), 'statut' => 'annule', 'motif' => 'Suivi traitement'],
            ['patient_id' => $patientIds[3], 'date_heure' => Carbon::today()->setTime(16, 0), 'statut' => 'termine', 'motif' => 'Bilan de sante'],
            ['patient_id' => $patientIds[0], 'date_heure' => Carbon::tomorrow()->setTime(11, 0), 'statut' => 'confirme', 'motif' => 'Lecture analyses'],
            ['patient_id' => $patientIds[1], 'date_heure' => Carbon::yesterday()->setTime(14, 30), 'statut' => 'termine', 'motif' => 'Consultation post-urgence'],
            ['patient_id' => $patientIds[0], 'date_heure' => Carbon::yesterday()->setTime(9, 30), 'statut' => 'termine', 'motif' => 'Suivi tension arterielle'],
        ];

        foreach ($rdvPayload as $rdv) {
            $rdvIds[] = DB::table('rendezvous')->insertGetId([
                'patient_id' => $rdv['patient_id'],
                'admin_id' => $medecinAdminId,
                'date_heure' => $rdv['date_heure']->toDateTimeString(),
                'duree_minutes' => 30,
                'motif' => $rdv['motif'],
                'statut' => $rdv['statut'],
                'rappel_envoye' => $rdv['statut'] === 'en_attente' ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $consultationOneId = DB::table('consultations')->insertGetId([
            'rdv_id' => $rdvIds[3],
            'patient_id' => $patientIds[3],
            'admin_id' => $medecinAdminId,
            'date' => Carbon::today()->setTime(16, 10)->toDateTimeString(),
            'symptomes' => 'Fatigue, maux de tete.',
            'diagnostic' => 'Syndrome viral leger.',
            'notes_medecin' => 'Repos 3 jours, bonne hydratation.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $consultationTwoId = DB::table('consultations')->insertGetId([
            'rdv_id' => $rdvIds[5],
            'patient_id' => $patientIds[1],
            'admin_id' => $medecinAdminId,
            'date' => Carbon::yesterday()->setTime(14, 40)->toDateTimeString(),
            'symptomes' => 'Toux seche et fievre moderee.',
            'diagnostic' => 'Infection ORL.',
            'notes_medecin' => 'Controle dans 7 jours.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $consultationThreeId = DB::table('consultations')->insertGetId([
            'rdv_id' => $rdvIds[6],
            'patient_id' => $patientIds[0],
            'admin_id' => $medecinAdminId,
            'date' => Carbon::yesterday()->setTime(9, 45)->toDateTimeString(),
            'symptomes' => 'Maux de tete matinaux et fatigue legere.',
            'diagnostic' => 'Hypertension legere a surveiller.',
            'notes_medecin' => 'Controle dans 2 semaines avec mesures a domicile.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $ordonnanceOneId = DB::table('ordonnances')->insertGetId([
            'consultation_id' => $consultationOneId,
            'admin_id' => $medecinAdminId,
            'date' => Carbon::today()->toDateString(),
            'instructions' => 'Prendre les medicaments apres les repas.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $ordonnanceTwoId = DB::table('ordonnances')->insertGetId([
            'consultation_id' => $consultationTwoId,
            'admin_id' => $medecinAdminId,
            'date' => Carbon::yesterday()->toDateString(),
            'instructions' => 'Boire beaucoup d eau et repos.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $ordonnanceThreeId = DB::table('ordonnances')->insertGetId([
            'consultation_id' => $consultationThreeId,
            'admin_id' => $medecinAdminId,
            'date' => Carbon::yesterday()->toDateString(),
            'instructions' => 'Reduire le sel, marcher 30 min par jour.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $medicamentParacetamolId = DB::table('medicaments')->insertGetId([
            'nom' => 'Paracetamol 1g',
            'nom_generique' => 'Paracetamol',
            'forme' => 'comprime',
            'description' => 'Antalgique et antipyretique.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $medicamentAmoxicillineId = DB::table('medicaments')->insertGetId([
            'nom' => 'Amoxicilline 500mg',
            'nom_generique' => 'Amoxicilline',
            'forme' => 'comprime',
            'description' => 'Antibiotique.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $medicamentSiropId = DB::table('medicaments')->insertGetId([
            'nom' => 'Sirop antitussif',
            'nom_generique' => 'Dextromethorphane',
            'forme' => 'sirop',
            'description' => 'Calme la toux seche.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('prescription')->insert([
            [
                'ordonnance_id' => $ordonnanceOneId,
                'medicament_id' => $medicamentParacetamolId,
                'posologie' => '1 comprime toutes les 8 heures',
                'observation' => 'Pendant 3 jours',
                'quantite' => 9,
                'duree_traitement' => '3 jours',
            ],
            [
                'ordonnance_id' => $ordonnanceTwoId,
                'medicament_id' => $medicamentAmoxicillineId,
                'posologie' => '1 comprime matin et soir',
                'observation' => 'Ne pas interrompre le traitement',
                'quantite' => 14,
                'duree_traitement' => '7 jours',
            ],
            [
                'ordonnance_id' => $ordonnanceTwoId,
                'medicament_id' => $medicamentSiropId,
                'posologie' => '10 ml, 3 fois par jour',
                'observation' => null,
                'quantite' => 1,
                'duree_traitement' => '5 jours',
            ],
            [
                'ordonnance_id' => $ordonnanceThreeId,
                'medicament_id' => $medicamentParacetamolId,
                'posologie' => '1 comprime si douleur',
                'observation' => 'Maximum 3 prises par jour',
                'quantite' => 12,
                'duree_traitement' => '7 jours',
            ],
        ]);

        $centreAnalyseId = DB::table('centres_radio_analyse')->insertGetId([
            'nom' => 'Lab Analyse Centrale',
            'type' => 'analyse',
            'adresse' => '123 Avenue Hassan II',
            'telephone' => '0537000001',
            'email' => 'contact@lab-centrale.ma',
            'ville' => 'Casablanca',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $centreRadioId = DB::table('centres_radio_analyse')->insertGetId([
            'nom' => 'Centre Radio Atlas',
            'type' => 'radiologie',
            'adresse' => '45 Boulevard Atlas',
            'telephone' => '0537000002',
            'email' => 'rdv@radio-atlas.ma',
            'ville' => 'Rabat',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('analyses')->insert([
            'consultation_id' => $consultationTwoId,
            'centre_id' => $centreAnalyseId,
            'type_analyse' => 'biologie',
            'date_analyse' => Carbon::today()->subDays(1)->toDateString(),
            'date_resultat' => Carbon::today()->toDateString(),
            'fichier' => 'analyses/resultat-biologie-001.pdf',
            'commentaire_medecin' => 'Parametres sanguins dans les normes.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('radiologies')->insert([
            'consultation_id' => $consultationOneId,
            'centre_id' => $centreRadioId,
            'type_radio' => 'Radiographie thorax',
            'date_examen' => Carbon::today()->toDateString(),
            'date_resultat' => Carbon::today()->addDay()->toDateString(),
            'fichier' => 'radiologies/radio-thorax-001.pdf',
            'interpretation' => 'Aucune anomalie majeure detectee.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('notifications')->insert([
            [
                'expediteur_id' => $secretaireUserId,
                'destinataire_id' => $patientUserIds[0],
                'type' => 'confirmation',
                'canal' => 'web',
                'titre' => 'Rendez-vous confirme',
                'contenu' => 'Votre rendez-vous de demain a 11:00 est confirme.',
                'lu' => 0,
                'date_envoi' => Carbon::now()->subHours(2)->toDateTimeString(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'expediteur_id' => $medecinUserId,
                'destinataire_id' => $patientUserIds[1],
                'type' => 'nouvelle_analyse',
                'canal' => 'email',
                'titre' => 'Resultat analyse disponible',
                'contenu' => 'Le resultat de votre analyse est disponible dans votre dossier.',
                'lu' => 0,
                'date_envoi' => Carbon::now()->subHour()->toDateTimeString(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'expediteur_id' => null,
                'destinataire_id' => $medecinUserId,
                'type' => 'systeme',
                'canal' => 'web',
                'titre' => 'Synchronisation terminee',
                'contenu' => 'Les donnees de test ont ete rechargees avec succes.',
                'lu' => 1,
                'date_envoi' => Carbon::now()->toDateTimeString(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Scenario etendu: plus de volume pour tester les listes, filtres et historiques.
        $extraPatients = [
            ['nom' => 'El Idrissi', 'prenom' => 'Nora', 'email' => 'nora.patient@medicabinet.ma', 'telephone' => '0626666666', 'cin' => 'IJ567890', 'ville' => 'Agadir', 'groupe' => 'O-'],
            ['nom' => 'Karimi', 'prenom' => 'Hamza', 'email' => 'hamza.patient@medicabinet.ma', 'telephone' => '0627777777', 'cin' => 'KL678901', 'ville' => 'Tanger', 'groupe' => 'AB+'],
            ['nom' => 'Lahlou', 'prenom' => 'Sara', 'email' => 'sara.patient@medicabinet.ma', 'telephone' => '0628888888', 'cin' => 'MN789012', 'ville' => 'Kenitra', 'groupe' => 'B-'],
            ['nom' => 'Alaoui', 'prenom' => 'Mehdi', 'email' => 'mehdi.patient@medicabinet.ma', 'telephone' => '0629999999', 'cin' => 'OP890123', 'ville' => 'Oujda', 'groupe' => 'A+'],
        ];

        foreach ($extraPatients as $index => $patientData) {
            $userId = DB::table('users')->insertGetId([
                'nom' => $patientData['nom'],
                'prenom' => $patientData['prenom'],
                'email' => $patientData['email'],
                'password' => Hash::make('password'),
                'telephone' => $patientData['telephone'],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $patientId = DB::table('patients')->insertGetId([
                'user_id' => $userId,
                'date_naissance' => Carbon::now()->subYears(25 + ($index * 3))->toDateString(),
                'cin' => $patientData['cin'],
                'adresse' => 'Adresse scenario etendu ' . ($index + 1),
                'ville' => $patientData['ville'],
                'groupe_sanguin' => $patientData['groupe'],
                'antecedents' => $index % 2 === 0 ? 'Migraine chronique legere.' : 'Aucun antecedent notable.',
                'antecedents_familiaux' => 'Hypertension familiale',
                'allergies' => $index === 2 ? 'Aspirine' : null,
                'poids_kg' => 58 + ($index * 5),
                'taille_cm' => 162 + ($index * 3),
                'traitement_en_cours' => $index === 1 ? 'Traitement antihypertenseur' : null,
                'date_creation_dossier' => Carbon::now()->subMonths(8 + $index)->toDateString(),
                'dossier_updated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $rdvPasseId = DB::table('rendezvous')->insertGetId([
                'patient_id' => $patientId,
                'admin_id' => $medecinAdminId,
                'date_heure' => Carbon::now()->subDays(10 + $index)->setTime(10, 0)->toDateTimeString(),
                'duree_minutes' => 30,
                'motif' => 'Consultation de suivi',
                'statut' => 'termine',
                'rappel_envoye' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('rendezvous')->insert([
                [
                    'patient_id' => $patientId,
                    'admin_id' => $medecinAdminId,
                    'date_heure' => Carbon::now()->addDays(3 + $index)->setTime(11, 30)->toDateTimeString(),
                    'duree_minutes' => 30,
                    'motif' => 'Controle periodique',
                    'statut' => 'confirme',
                    'rappel_envoye' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'patient_id' => $patientId,
                    'admin_id' => $medecinAdminId,
                    'date_heure' => Carbon::now()->addDays(6 + $index)->setTime(15, 0)->toDateTimeString(),
                    'duree_minutes' => 30,
                    'motif' => 'Demande de bilan complementaire',
                    'statut' => 'en_attente',
                    'rappel_envoye' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $consultationId = DB::table('consultations')->insertGetId([
                'rdv_id' => $rdvPasseId,
                'patient_id' => $patientId,
                'admin_id' => $medecinAdminId,
                'date' => Carbon::now()->subDays(10 + $index)->setTime(10, 20)->toDateTimeString(),
                'symptomes' => 'Fatigue et douleurs musculaires.',
                'diagnostic' => 'Syndrome grippal resolu.',
                'notes_medecin' => 'Suivi recommande apres 1 mois.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $ordonnanceId = DB::table('ordonnances')->insertGetId([
                'consultation_id' => $consultationId,
                'admin_id' => $medecinAdminId,
                'date' => Carbon::now()->subDays(10 + $index)->toDateString(),
                'instructions' => 'Respecter la posologie et faire un controle dans 30 jours.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('prescription')->insert([
                'ordonnance_id' => $ordonnanceId,
                'medicament_id' => $medicamentParacetamolId,
                'posologie' => '1 comprime matin et soir si douleur',
                'observation' => 'Arreter si amelioration complete',
                'quantite' => 10,
                'duree_traitement' => '5 jours',
            ]);

            if ($index % 2 === 0) {
                DB::table('analyses')->insert([
                    'consultation_id' => $consultationId,
                    'centre_id' => $centreAnalyseId,
                    'type_analyse' => 'biologie',
                    'date_analyse' => Carbon::now()->subDays(9 + $index)->toDateString(),
                    'date_resultat' => Carbon::now()->subDays(7 + $index)->toDateString(),
                    'fichier' => 'analyses/resultat-extra-' . ($index + 1) . '.pdf',
                    'commentaire_medecin' => 'Resultats sans anomalie significative.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('radiologies')->insert([
                    'consultation_id' => $consultationId,
                    'centre_id' => $centreRadioId,
                    'type_radio' => 'Echographie abdominale',
                    'date_examen' => Carbon::now()->subDays(9 + $index)->toDateString(),
                    'date_resultat' => Carbon::now()->subDays(8 + $index)->toDateString(),
                    'fichier' => 'radiologies/radio-extra-' . ($index + 1) . '.pdf',
                    'interpretation' => 'Compte rendu stable.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('notifications')->insert([
                [
                    'expediteur_id' => $secretaireUserId,
                    'destinataire_id' => $userId,
                    'type' => 'rdv_rappel',
                    'canal' => 'web',
                    'titre' => 'Rappel de rendez-vous',
                    'contenu' => 'Vous avez un rendez-vous confirme dans les prochains jours.',
                    'lu' => 0,
                    'date_envoi' => Carbon::now()->subMinutes(30 + ($index * 5))->toDateTimeString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'expediteur_id' => $medecinUserId,
                    'destinataire_id' => $userId,
                    'type' => 'message',
                    'canal' => 'web',
                    'titre' => 'Suivi medical',
                    'contenu' => 'Pensez a mettre a jour vos constantes avant le prochain rendez-vous.',
                    'lu' => $index % 2,
                    'date_envoi' => Carbon::now()->subMinutes(15 + ($index * 4))->toDateTimeString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        $this->command?->info('Seeders complets charges: roles, patients, RDV, consultations, ordonnances, analyses, radiologies, notifications.');
    }
}
