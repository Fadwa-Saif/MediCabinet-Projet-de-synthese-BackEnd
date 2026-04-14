<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analyse extends Model
{
    use HasFactory;

    protected $fillable = [
         'patient_id',
        'consultation_id',
         'type_analyse',
         'laboratoire',
        'centre_id',
        'type_analyse',
        'date_analyse',
        'date_resultat',
        'fichier',
        'commentaire_medecin',
        'commentaire_patient',
    ];

    protected function casts(): array
    {
        return [
            'date_analyse' => 'date',
            'date_resultat' => 'date',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function centre(): BelongsTo
    {
        return $this->belongsTo(CentreRadioAnalyse::class, 'centre_id');
    }
}
