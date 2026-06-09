<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        // Existing fields (do not remove)
        'rdv_id',
        'patient_id',
        'admin_id',
        'date',
        'symptomes',
        'diagnostic',
        'notes_medecin',

        // EN-TÊTE
        'type_consultation',

        // SECTION S: SUBJECTIF
        'motif',
        'histoire_maladie',
        'douleur_presente',
        'douleur_localisation',
        'douleur_type',
        'douleur_intensite_eva',
        'symptomes_associes',

        // SECTION O: OBJECTIF — SIGNES VITAUX
        'tension_arterielle_sys',
        'tension_arterielle_dia',
        'frequence_cardiaque',
        'temperature',
        'spo2',
        'frequence_respiratoire',
        'poids',
        'taille',
        'imc',

        // SECTION O: OBJECTIF — EXAMEN CLINIQUE
        'examen_etat_general',
        'examen_cardiovasculaire',
        'examen_respiratoire',
        'examen_abdomen',
        'examen_neurologique',
        'examen_osteomusculaire',
        'examen_peau_muqueuses',
        'examen_autres',

        // SECTION A: ÉVALUATION
        'diagnostic_code_cim10',
        'diagnostics_secondaires',
        'raisonnement_clinique',

        // SECTION P: PLAN THÉRAPEUTIQUE
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
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            // SOAP JSON fields
            'symptomes_associes' => 'array',
            'diagnostics_secondaires' => 'array',
            // Boolean fields
            'douleur_presente' => 'boolean',
            'refere_specialiste' => 'boolean',
            'hospitalisation' => 'boolean',
            'arret_travail' => 'boolean',
            // Date fields
            'arret_travail_date_debut' => 'date',
            'prochain_rdv' => 'date',
        ];
    }

    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(RendezVous::class, 'rdv_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function ordonnances(): HasMany
    {
        return $this->hasMany(Ordonnance::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analyse::class);
    }

    public function radiologies(): HasMany
    {
        return $this->hasMany(Radiologie::class);
    }
}
