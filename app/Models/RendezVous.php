<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RendezVous extends Model
{
    use HasFactory;

    protected $table = 'rendezvous';

    protected $fillable = [
        'patient_id',
        'admin_id',
        'date_heure',
        'duree_minutes',
        'motif',
        'statut',
        'rappel_envoye',
    ];

    protected function casts(): array
    {
        return [
            'date_heure' => 'datetime',
            'rappel_envoye' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class, 'rdv_id');
    }

    public function scopeConfirme(Builder $query): Builder
    {
        return $query->where('statut', 'confirme');
    }

    public function scopeAVenir(Builder $query): Builder
    {
        return $query->where('date_heure', '>', now());
    }

    public function confirmer(): bool
    {
        return $this->update(['statut' => 'confirme']);
    }

    public function annuler(): bool
    {
        return $this->update(['statut' => 'annule']);
    }

    public function terminer(): bool
    {
        return $this->update(['statut' => 'termine']);
    }
}
