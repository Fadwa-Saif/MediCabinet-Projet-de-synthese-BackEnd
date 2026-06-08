<?php

namespace App\Models;

use App\Models\SecretaryMedecin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'matricule',
        'biographie',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disponibilites(): HasMany
    {
        return $this->hasMany(Disponibilite::class);
    }

    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function ordonnances(): HasMany
    {
        return $this->hasMany(Ordonnance::class);
    }

    public function patients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'patient_medecin', 'medecin_id', 'patient_id')
            ->withPivot('date_affectation', 'statut')
            ->withTimestamps();
    }

    public function secretaryRequests(): HasMany
    {
        return $this->hasMany(SecretaryMedecin::class, 'medecin_id');
    }

    public function secretaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'secretary_medecin', 'medecin_id', 'secretary_id')
            ->withPivot('statut', 'date_demande', 'date_decision', 'motif_refus')
            ->withTimestamps();
    }

    public function isMedecin(): bool
    {
        return $this->role === 'medecin';
    }

    public function isSecretaire(): bool
    {
        return $this->role === 'secretaire';
    }
}
