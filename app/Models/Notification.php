<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'expediteur_id',
        'destinataire_id',
        'type',
        'canal',
        'titre',
        'contenu',
        'lu',
        'date_envoi',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
            'date_envoi' => 'datetime',
        ];
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function scopeNonLues(Builder $query): Builder
    {
        return $query->where('lu', 0);
    }

    public function marquerLu(): bool
    {
        return $this->update(['lu' => 1]);
    }
}
