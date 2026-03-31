<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Radiologie extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'centre_id',
        'type_radio',
        'date_examen',
        'date_resultat',
        'fichier',
        'interpretation',
    ];

    protected function casts(): array
    {
        return [
            'date_examen' => 'date',
            'date_resultat' => 'date',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function centre(): BelongsTo
    {
        return $this->belongsTo(CentreRadioAnalyse::class, 'centre_id');
    }
}
