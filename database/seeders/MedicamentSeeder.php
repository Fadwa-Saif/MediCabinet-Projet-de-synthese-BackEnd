<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicaments = [
            // Analgésiques et anti-inflammatoires
            ['nom' => 'Doliprane 500mg', 'nom_generique' => 'Paracétamol', 'forme' => 'comprime', 'description' => 'Analgésique et antipyrétique'],
            ['nom' => 'Doliprane 1000mg', 'nom_generique' => 'Paracétamol', 'forme' => 'comprime', 'description' => 'Analgésique et antipyrétique'],
            ['nom' => 'Nurofen 200mg', 'nom_generique' => 'Ibuprofène', 'forme' => 'comprime', 'description' => 'Anti-inflammatoire non stéroïdien'],
            ['nom' => 'Aspirine 500mg', 'nom_generique' => 'Acide acétylsalicylique', 'forme' => 'comprime', 'description' => 'Analgésique et anti-inflammatoire'],
            
            // Antibiotiques
            ['nom' => 'Augmentin 500mg', 'nom_generique' => 'Amoxicilline + Acide clavulanique', 'forme' => 'comprime', 'description' => 'Antibiotique à large spectre'],
            ['nom' => 'Amoxicilline 500mg', 'nom_generique' => 'Amoxicilline', 'forme' => 'comprime', 'description' => 'Antibiotique bêta-lactame'],
            ['nom' => 'Azithromycine 250mg', 'nom_generique' => 'Azithromycine', 'forme' => 'comprime', 'description' => 'Macrolide antibiotique'],
            ['nom' => 'Ciprofoxacine 500mg', 'nom_generique' => 'Ciprofloxacine', 'forme' => 'comprime', 'description' => 'Fluoroquinolone antibiotique'],
            ['nom' => 'Cephalexine 500mg', 'nom_generique' => 'Céphalexine', 'forme' => 'comprime', 'description' => 'Céphalosporine antibiotique'],
            
            // Antihistaminiques et allergies
            ['nom' => 'Dexeryl', 'nom_generique' => 'Glycérol', 'forme' => 'comprime', 'description' => 'Hydratant pour la peau'],
            ['nom' => 'Polaramine 2mg', 'nom_generique' => 'Dexchlorophéniramine', 'forme' => 'comprime', 'description' => 'Antihistaminique'],
            ['nom' => 'Allegra 120mg', 'nom_generique' => 'Fexofénadine', 'forme' => 'comprime', 'description' => 'Antihistaminique non sédatif'],
            
            // Antihypertenseurs
            ['nom' => 'Enalapril 5mg', 'nom_generique' => 'Énalapril', 'forme' => 'comprime', 'description' => 'Inhibiteur ACE'],
            ['nom' => 'Metoprolol 100mg', 'nom_generique' => 'Métoprolol', 'forme' => 'comprime', 'description' => 'Bêta-bloquant'],
            ['nom' => 'Amlodipine 5mg', 'nom_generique' => 'Amlodipine', 'forme' => 'comprime', 'description' => 'Inhibiteur calcique'],
            
            // Antidiabétiques
            ['nom' => 'Metformine 500mg', 'nom_generique' => 'Metformine', 'forme' => 'comprime', 'description' => 'Traitement du diabète type 2'],
            ['nom' => 'Glibenclamide 5mg', 'nom_generique' => 'Glibenclamide', 'forme' => 'comprime', 'description' => 'Sécrétagogue d\'insuline'],
            
            // Antiémétiques et antispasmodiques
            ['nom' => 'Motilium 10mg', 'nom_generique' => 'Dompéridone', 'forme' => 'comprime', 'description' => 'Antiémétique'],
            ['nom' => 'Buscopan 10mg', 'nom_generique' => 'Hyoscine', 'forme' => 'comprime', 'description' => 'Antispasmodique'],
            
            // Antihistaminiques H2 et antiacides
            ['nom' => 'Ranitidine 150mg', 'nom_generique' => 'Ranitidine', 'forme' => 'comprime', 'description' => 'Inhibiteur H2 pour les ulcères'],
            ['nom' => 'Oméprazole 20mg', 'nom_generique' => 'Oméprazole', 'forme' => 'comprime', 'description' => 'Inhibiteur de la pompe à protons'],
            
            // Antitussifs
            ['nom' => 'Actifed', 'nom_generique' => 'Triprolidine + Pseudoéphédrine', 'forme' => 'sirop', 'description' => 'Antitussif et décongestionnant'],
            ['nom' => 'Bronchicum', 'nom_generique' => 'Thym', 'forme' => 'sirop', 'description' => 'Expectorant à base de plantes'],
            
            // Vitamines et suppléments
            ['nom' => 'Vitamine D3 1000UI', 'nom_generique' => 'Cholécalciférol', 'forme' => 'comprime', 'description' => 'Supplément vitaminique'],
            ['nom' => 'Vitamine C 500mg', 'nom_generique' => 'Acide ascorbique', 'forme' => 'comprime', 'description' => 'Supplément vitaminique'],
            ['nom' => 'Magnésium 300mg', 'nom_generique' => 'Magnésium', 'forme' => 'comprime', 'description' => 'Supplément minéral'],
            
            // Injections
            ['nom' => 'Intramuscular Injection 500mg', 'nom_generique' => 'Penicilline G', 'forme' => 'injection', 'description' => 'Antibiotique injectable'],
            ['nom' => 'Diclofénac 75mg', 'nom_generique' => 'Diclofénac', 'forme' => 'injection', 'description' => 'Anti-inflammatoire injectable'],
            
            // Antifongiques et antiparasitaires
            ['nom' => 'Fluconazole 150mg', 'nom_generique' => 'Fluconazole', 'forme' => 'comprime', 'description' => 'Antifongique'],
            ['nom' => 'Mébendazole 100mg', 'nom_generique' => 'Mébendazole', 'forme' => 'comprime', 'description' => 'Antiparasitaire'],
            
            // Laxatifs et anti-diarrhéiques
            ['nom' => 'Imodium 2mg', 'nom_generique' => 'Lopéramide', 'forme' => 'comprime', 'description' => 'Anti-diarrhéique'],
            ['nom' => 'Bisacodyl 5mg', 'nom_generique' => 'Bisacodyl', 'forme' => 'comprime', 'description' => 'Laxatif stimulant'],
            
            // Anxiolytiques et sédatifs
            ['nom' => 'Diazépam 5mg', 'nom_generique' => 'Diazépam', 'forme' => 'comprime', 'description' => 'Anxiolytique'],
            ['nom' => 'Buspirone 5mg', 'nom_generique' => 'Buspirone', 'forme' => 'comprime', 'description' => 'Anxiolytique azaspirone'],
        ];

        foreach ($medicaments as $medicament) {
            DB::table('medicaments')->insertOrIgnore([
                'nom' => $medicament['nom'],
                'nom_generique' => $medicament['nom_generique'],
                'forme' => $medicament['forme'],
                'description' => $medicament['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
