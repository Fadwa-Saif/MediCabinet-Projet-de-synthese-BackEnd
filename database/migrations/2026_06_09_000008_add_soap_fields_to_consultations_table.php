<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds SOAP (Subjective, Objective, Assessment, Plan) fields
     * to the consultations table. All columns are nullable to prevent breaking
     * existing records.
     *
     * IMPORTANT NOTES:
     * - Prescriptions (dose, frequence, duree) are already stored in the `prescription` pivot table
     * - Analyses are already stored in the `analyses` table
     * - Radiologies are already stored in the `radiologies` table
     * - This migration only adds fields that have no home in related tables
     */
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // ─── EN-TÊTE (Header) ───────────────────────────────────────────
            $table->string('type_consultation', 100)->nullable()->comment('Première consultation, Suivi, Urgence, etc.');

            // ─── SECTION S: SUBJECTIF ───────────────────────────────────────
            $table->string('motif', 255)->nullable()->comment('Chief complaint / reason for visit');
            $table->text('histoire_maladie')->nullable()->comment('Patient history of present illness');

            // Pain assessment (EVA: Échelle Visuelle Analogique 0-10)
            $table->boolean('douleur_presente')->nullable()->comment('Is patient experiencing pain?');
            $table->string('douleur_localisation', 255)->nullable()->comment('Where is the pain located?');
            $table->string('douleur_type', 100)->nullable()->comment('Type of pain: acute, chronic, burning, etc.');
            $table->unsignedTinyInteger('douleur_intensite_eva')->nullable()->comment('Pain intensity 0-10');

            // Associated symptoms (stored as JSON array)
            $table->json('symptomes_associes')->nullable()->comment('Array of associated symptoms');

            // ─── SECTION O: OBJECTIF — SIGNES VITAUX ────────────────────────
            $table->unsignedSmallInteger('tension_arterielle_sys')->nullable()->comment('Systolic BP (mmHg)');
            $table->unsignedSmallInteger('tension_arterielle_dia')->nullable()->comment('Diastolic BP (mmHg)');
            $table->unsignedSmallInteger('frequence_cardiaque')->nullable()->comment('Heart rate (bpm)');
            $table->decimal('temperature', 4, 1)->nullable()->comment('Body temperature (°C)');
            $table->unsignedTinyInteger('spo2')->nullable()->comment('Oxygen saturation (%)');
            $table->unsignedTinyInteger('frequence_respiratoire')->nullable()->comment('Respiratory rate (breaths/min)');

            // Anthropometric measurements
            $table->decimal('poids', 5, 1)->nullable()->comment('Weight (kg)');
            $table->unsignedSmallInteger('taille')->nullable()->comment('Height (cm)');
            $table->decimal('imc', 4, 1)->nullable()->comment('BMI (kg/m²) — calculated client-side');

            // ─── SECTION O: OBJECTIF — EXAMEN CLINIQUE ──────────────────────
            $table->text('examen_etat_general')->nullable()->comment('General appearance and well-being');
            $table->text('examen_cardiovasculaire')->nullable()->comment('Cardiovascular examination findings');
            $table->text('examen_respiratoire')->nullable()->comment('Respiratory examination findings');
            $table->text('examen_abdomen')->nullable()->comment('Abdominal examination findings');
            $table->text('examen_neurologique')->nullable()->comment('Neurological examination findings');
            $table->text('examen_osteomusculaire')->nullable()->comment('Musculoskeletal examination findings');
            $table->text('examen_peau_muqueuses')->nullable()->comment('Skin and mucous membranes examination');
            $table->text('examen_autres')->nullable()->comment('Other clinical observations');

            // ─── SECTION A: ÉVALUATION ──────────────────────────────────────
            // NOTE: Prescriptions for analyses and radiologies are handled by
            // separate tables (analyses, radiologies) via HasMany relationships
            $table->string('diagnostic_code_cim10', 50)->nullable()->comment('Primary diagnosis ICD-10 code');
            $table->json('diagnostics_secondaires')->nullable()->comment('Array of secondary diagnoses with codes');
            $table->text('raisonnement_clinique')->nullable()->comment('Clinical reasoning and differential diagnosis');

            // ─── SECTION P: PLAN THÉRAPEUTIQUE ──────────────────────────────
            // NOTE: Prescriptions (medicaments) are handled by ordonnances + prescription pivot table
            // NOTE: Analyses are prescribed via separate AnalyseController::prescrire()
            // NOTE: Radiologies are prescribed separately (similar to analyses)

            // Referral and orientation
            $table->boolean('refere_specialiste')->nullable()->comment('Is specialist referral needed?');
            $table->string('refere_specialite', 100)->nullable()->comment('Specialty name for referral');
            $table->string('refere_urgence', 50)->nullable()->comment('Urgency level: urgent, semi-urgent, routine');

            // Hospitalization
            $table->boolean('hospitalisation')->nullable()->comment('Is hospitalization needed?');
            $table->string('hospitalisation_service', 100)->nullable()->comment('Hospital service/department');

            // Work-related follow-up
            $table->boolean('arret_travail')->nullable()->comment('Is sick leave needed?');
            $table->unsignedSmallInteger('arret_travail_duree')->nullable()->comment('Sick leave duration (days)');
            $table->date('arret_travail_date_debut')->nullable()->comment('Sick leave start date');

            // Follow-up scheduling
            $table->date('prochain_rdv')->nullable()->comment('Date for next appointment');
            $table->text('instructions_patient')->nullable()->comment('Patient instructions and follow-up care');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // EN-TÊTE
            $table->dropColumn('type_consultation');

            // SECTION S
            $table->dropColumn([
                'motif',
                'histoire_maladie',
                'douleur_presente',
                'douleur_localisation',
                'douleur_type',
                'douleur_intensite_eva',
                'symptomes_associes',
            ]);

            // SECTION O — Vital Signs
            $table->dropColumn([
                'tension_arterielle_sys',
                'tension_arterielle_dia',
                'frequence_cardiaque',
                'temperature',
                'spo2',
                'frequence_respiratoire',
                'poids',
                'taille',
                'imc',
            ]);

            // SECTION O — Clinical Exam
            $table->dropColumn([
                'examen_etat_general',
                'examen_cardiovasculaire',
                'examen_respiratoire',
                'examen_abdomen',
                'examen_neurologique',
                'examen_osteomusculaire',
                'examen_peau_muqueuses',
                'examen_autres',
            ]);

            // SECTION A
            $table->dropColumn([
                'diagnostic_code_cim10',
                'diagnostics_secondaires',
                'raisonnement_clinique',
            ]);

            // SECTION P
            $table->dropColumn([
                'refere_specialiste',
                'refere_specialite',
                'refere_urgence',
                'hospitalisation',
                'hospitalisation_service',
                'arret_travail',
                'arret_travail_duree',
                'arret_travail_date_debut',
                'prochain_rdv',
                'instructions_patient',
            ]);
        });
    }
};
