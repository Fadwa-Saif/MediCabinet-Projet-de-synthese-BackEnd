<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdonnanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Récupérer les consultations
        $consultations = DB::table('consultations')
            ->orderBy('id')
            ->take(20)
            ->get();

        $adminIds = DB::table('admins')
            ->where('role', 'medecin')
            ->pluck('id')
            ->toArray();

        if (empty($adminIds)) {
            return;
        }

        $instructions = [
            'Prendre les médicaments après les repas',
            'Boire beaucoup d\'eau pendant le traitement',
            'Repos absolu pendant 3 jours',
            'Éviter l\'alcool pendant le traitement',
            'Prendre à jeun le matin',
            'À prendre avec un verre d\'eau',
            'Continuer le traitement pendant 7 jours',
            'Ne pas dépasser la dose prescrite',
            'Prendre avec les repas pour éviter les nausées',
            'En cas d\'effet secondaire, arrêter et consulter',
            'Garder à température ambiante',
            'À l\'abri de la lumière et de l\'humidité',
            'Contrôle dans 1 semaine',
            'Si aggravation, retour immédiat',
            'Suivi ambulatoire obligatoire',
            'Régime sans sel recommandé',
            'Marcher 30 minutes par jour si possible',
            'Appliquer localement 2 fois par jour',
            'Garder à portée de main',
            'Consultation de suivi recommandée',
        ];

        $medicamentsAvailables = DB::table('medicaments')
            ->pluck('id')
            ->toArray();

        if (empty($medicamentsAvailables)) {
            return;
        }

        $posologiesExemples = [
            '1 comprimé x 3 fois par jour',
            '2 comprimés x 2 fois par jour',
            '1 comprimé x 2 fois par jour',
            '3 comprimés x 1 fois par jour',
            '1/2 comprimé x 3 fois par jour',
            '1 comprimé le matin à jeun',
            '1 comprimé le soir avant le coucher',
            '1 comprimé toutes les 6 heures',
            '1 comprimé toutes les 8 heures',
            '1 cuillère à café x 2 fois par jour',
            '2 cuillères à café x 3 fois par jour',
            '1 injection intramusculaire par jour',
            '1 ampoule x 2 fois par jour',
            'Appliquer localement 2 fois par jour',
            '5ml x 3 fois par jour',
            '10ml avant les repas',
            '1/4 de comprimé x 3 fois par jour',
            '2 comprimés x 3 fois par jour',
            '1 gélule x 2 fois par jour',
            ' 1 suppositoire x 2 fois par jour',
        ];

        $durees = [
            '5 jours',
            '7 jours',
            '10 jours',
            '15 jours',
            '21 jours',
            '1 mois',
            '2 mois',
            '3 mois',
            '6 mois',
            'Au besoin',
            'Continu',
            'Jusqu\'à disparition des symptômes',
            '14 jours',
        ];

        $observations = [
            'Médicament important, à ne pas oublier',
            'Si nausées, prendre avec du lait',
            'Attention aux interactions',
            'À continuer régulièrement',
            'Efficace après 3-5 jours',
            'Gestion symptomatique',
            'Peut causer des vertiges',
            'Utiliser de préférence au repas',
            'Complémentaire au premier médicament',
            'Pas d\'automédication',
            'Surveillance nécessaire',
            'Effets rapides attendus',
            'À adapter selon l\'effet',
            'Peut être substitué si allergie',
            null,
        ];

        $quantites = [1, 1, 2, 3, 1, 1, 1, 2, 2, 1, 1, 2, 1, 1, 1];

        foreach ($consultations as $consultation) {
            $adminId = $adminIds[array_rand($adminIds)];
            
            // Créer l'ordonnance
            $ordonnanceId = DB::table('ordonnances')->insertGetId([
                'consultation_id' => $consultation->id,
                'admin_id' => $adminId,
                'date' => Carbon::parse($consultation->date)->toDateString(),
                'instructions' => $instructions[array_rand($instructions)],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Ajouter 2-5 médicaments à cette ordonnance
            $nbMedicaments = rand(2, 5);
            $medicamentsSelected = array_rand($medicamentsAvailables, min($nbMedicaments, count($medicamentsAvailables)));
            
            if (!is_array($medicamentsSelected)) {
                $medicamentsSelected = [$medicamentsSelected];
            }

            foreach ($medicamentsSelected as $medicamentIndex) {
                $medicamentId = $medicamentsAvailables[$medicamentIndex];
                
                DB::table('prescription')->insert([
                    'ordonnance_id' => $ordonnanceId,
                    'medicament_id' => $medicamentId,
                    'posologie' => $posologiesExemples[array_rand($posologiesExemples)],
                    'observation' => $observations[array_rand($observations)],
                    'quantite' => $quantites[array_rand($quantites)],
                    'duree_traitement' => $durees[array_rand($durees)],
                ]);
            }
        }
    }
}
