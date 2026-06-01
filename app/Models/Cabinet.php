<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cabinet extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'adresse',
        'ville',
        'specialite',
        'docteur_id',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docteur_id');
    }
}