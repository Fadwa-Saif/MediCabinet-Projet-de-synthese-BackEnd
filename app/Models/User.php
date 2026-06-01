<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'is_active',
        'photo_profil',
        'cabinet_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password'  => 'hashed',
    ];

    // ── JWT ──────────────────────────────────────────────────────────────
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    // ── Relations ────────────────────────────────────────────────────────
    public function admin(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function patient(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function cabinet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function cabinetOwned(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Cabinet::class, 'docteur_id');
    }

    public function notificationsRecues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class, 'destinataire_id');
    }

    public function notificationsEnvoyees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class, 'expediteur_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->admin()->exists();
    }

    public function isPatient(): bool
    {
        return $this->patient()->exists();
    }

    public function isMedecin(): bool
    {
        return $this->admin?->role === 'medecin';
    }

    public function isSecretaire(): bool
    {
        return $this->admin?->role === 'secretaire';
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}