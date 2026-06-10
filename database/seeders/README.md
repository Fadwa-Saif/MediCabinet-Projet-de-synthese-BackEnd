# Seeders MediCabinet - Données Marocaines Réalistes

## Description

Ce dossier contient des seeders Laravel pour remplir la base de données de MediCabinet avec des données marocaines réalistes. Les données incluent des noms marocains authentiques, des téléphones au format +212, des montants en DH, et des adresses de villes marocaines principales.

## Seeders Disponibles

### 1. **VilleSeeder.php**
Crée 15 villes marocaines principales avec codes régionaux :
- Casablanca, Rabat, Fès, Marrakech, Tanger, Agadir, Meknes, Salé, Oujda, Kenitra, Tetouan, El Jadida, Safi, Laayoune, Azrou

### 2. **SpecialiteSeeder.php**
Crée 15 spécialités médicales :
- Médecine Générale, Cardiologie, Pédiatrie, Dermatologie, ORL, Orthopédie, Gynécologie, Pneumologie, Neurologie, Gastroentérologie, Ophtalmologie, Psychiatrie, Dentisterie, Radiologie, Chirurgie

### 3. **MedicamentSeeder.php**
Crée 35+ médicaments courants au Maroc :
- Analgésiques : Doliprane, Nurofen, Aspirine
- Antibiotiques : Augmentin, Amoxicilline, Azithromycine, Ciprofloxacine, Cephalexine
- Antihistaminiques : Dexeryl, Polaramine, Allegra
- Antihypertenseurs : Enalapril, Metoprolol, Amlodipine
- Antidiabétiques : Metformine, Glibenclamide
- Et bien d'autres catégories

### 4. **MoroccanUserSeeder.php**
Crée les utilisateurs avec noms marocains :
- **15 Médecins** : Benali Ahmed, El Amrani Sara, Bennani Fatima, Tazi Mohamed, etc.
- **5 Secrétaires** : Chater Basma, Makoudi Amina, Saadi Hind, etc.
- **30 Patients** : El Fassi Houda, Kassim Jamal, Saif Fadwa, etc.

Tous les téléphones au format +212 6XX-XXXXXX

### 5. **AdminSeeder.php**
Crée les enregistrements admin liés aux utilisateurs :
- 15 administrateurs médecins avec matricules MED-001 à MED-015
- 5 administrateurs secrétaires avec matricules SEC-001 à SEC-005

### 6. **PatientSeeder.php**
Crée les profils patients avec :
- **30 patients** avec profils complets
- CIN marocains uniques (AB123456, CD234567, etc.)
- Dates de naissance variées (1950-2015)
- Groupes sanguins diversifiés
- Adresses réalistes dans les villes marocaines
- Antécédents médicaux et familiaux
- Allergies et traitements en cours

### 7. **CabinetSeeder.php**
Crée **15 cabinets médicaux** :
- Un cabinet par médecin
- Adresses réalistes dans : Casablanca, Rabat, Fès, Marrakech, Tanger
- Spécialités médicales variées
- Noms de cabinets professionnels

### 8. **RendezvousSeeder.php**
Crée **40+ rendez-vous** :
- Distribution réaliste : 30% passés, 10% aujourd'hui, 60% futurs
- Heures entre 9h-17h
- Motifs variés : consultations, bilans, lectures d'analyses, etc.
- Statuts : en_attente, confirme, annule, termine

### 9. **ConsultationSeeder.php**
Crée **20 consultations** liées aux rendez-vous terminés :
- Symptômes réalistes
- Diagnostics variés
- Notes médicales
- Lié aux rendez-vous et consultations

### 10. **OrdonnanceSeeder.php**
Crée **20 ordonnances** :
- 2-5 médicaments par ordonnance
- Posologies détaillées
- Durées de traitement réalistes
- Instructions de suivi
- Observations pharmaceutiques

### 11. **AnalyseSeeder.php**
Crée **20 analyses** :
- Types d'analyses variées (NFS, bilan lipidique, glycémie, etc.)
- Laboratoires marocains réalistes
- Dates d'analyse et de résultats
- Commentaires patient et médecin

## Structure de Données

### Montants en DH (Dirham marocain)
Les montants typiques pour les services médicaux sont :
- 200-350 DH : consultation générale
- 400-600 DH : consultation spécialiste
- 100-200 DH : analyses
- 150-400 DH : imagerie

### Format Téléphone
Tous les téléphones utilisent le format marocain standard :
- `+212 6XX-XXXXXX` pour les patients/médecins
- Commence par `+212 61x` à `+212 69x`

### Noms Marocains Réalistes
Les noms incluent les noms de famille marocains courants :
- Ben + nom (Bennani, Benali)
- El + nom (El Amrani, El Fassi, El Idrissi, Elmir)
- Prénoms tamazight/arabes courants

### Villes Marocaines
15 principales villes avec régions :
- Casablanca-Settat
- Rabat-Salé-Kénitra
- Fès-Meknès
- Marrakech-Safi
- Tanger-Tétouan-Al Hoceïma
- Souss-Massa
- Oriental

## Utilisation

### Exécuter tous les seeders
```bash
php artisan db:seed --class=DatabaseSeeder
```

### Exécuter un seeder spécifique
```bash
php artisan db:seed --class=VilleSeeder
php artisan db:seed --class=MedicamentSeeder
php artisan db:seed --class=MoroccanUserSeeder
```

### Réinitialiser la base et seeder
```bash
php artisan migrate:refresh --seed
```

## Connexions et Créd Identifiants

### Compte Administrateur
- **Email** : benali.ahmed@medicabinet.ma
- **Mot de passe** : Password123!
- **Rôle** : Médecin

### Compte Secrétaire
- **Email** : chater.basma@medicabinet.ma
- **Mot de passe** : Password123!
- **Rôle** : Secrétaire

### Comptes Patients
Tous les patients ont le mot de passe : `Password123!`
Emails : `{prenom}.{nom}@patient.ma`

## Quantités de Données

| Table | Nombre | Détails |
|-------|--------|---------|
| Villes | 15 | Toutes les régions marocaines |
| Spécialités | 15 | Spécialités médicales variées |
| Médicaments | 35+ | Catégories variées |
| Utilisateurs | 50 | 15 médecins, 5 secrétaires, 30 patients |
| Admins | 20 | 15 médecins, 5 secrétaires |
| Patients | 30 | Profils complets avec antécédents |
| Cabinets | 15 | Un par médecin |
| Rendez-vous | 40+ | Distribution passé/futur réaliste |
| Consultations | 20 | Liées aux rendez-vous |
| Ordonnances | 20 | Avec prescriptions détaillées |
| Analyses | 20 | Types variés avec résultats |

## Caractéristiques des Données

✅ **Noms marocains authentiques**
✅ **Téléphones au format +212**
✅ **CIN marocains réalistes**
✅ **Montants en DH**
✅ **Adresses dans villes réelles**
✅ **Dates cohérentes**
✅ **Relations cohérentes entre entités**
✅ **Au moins 20 enregistrements par table**
✅ **Données médicales réalistes**

## Notes Importantes

1. Les seeders utilisent **Cache** pour stocker les IDs intermédiaires entre les exécutions
2. Les relations entre tables sont respectées (foreign keys)
3. Les dates de rendez-vous sont distribuées réalistes (passés/futurs)
4. Tous les montants sont en DH (Dirham marocain)
5. Les noms suivent les conventions marocaines
6. Les spécialités et médicaments correspondent à la pratique marocaine

## Troubleshooting

Si vous rencontrez une erreur de table inexistante (villes, spécialites) :
1. Vérifiez que les migrations ont été exécutées
2. Créez les migrations si nécessaire :
   ```bash
   php artisan make:migration create_villes_table
   php artisan make:migration create_specialites_table
   ```

## Auteur

Seeders créés pour le projet MediCabinet - Système de gestion de cabinet médical marocain.
