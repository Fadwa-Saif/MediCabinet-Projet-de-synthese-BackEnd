<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_naissance',
        'cin',
        'adresse',
        'ville',
        'groupe_sanguin',
        'antecedents',
        'antecedents_familiaux',
        'allergies',
        'poids_kg',
        'taille_cm',
        'traitement_en_cours',
        'date_creation_dossier',
        'dossier_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_creation_dossier' => 'date',
            'dossier_updated_at' => 'datetime',
            'poids_kg' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance?->age;
    }

    public function getImcAttribute(): ?float
    {
        if (empty($this->poids_kg) || empty($this->taille_cm)) {
            return null;
        }

        $tailleMetres = $this->taille_cm / 100;

        if ($tailleMetres <= 0) {
            return null;
        }

        return round($this->poids_kg / ($tailleMetres * $tailleMetres), 1);
    }
}
