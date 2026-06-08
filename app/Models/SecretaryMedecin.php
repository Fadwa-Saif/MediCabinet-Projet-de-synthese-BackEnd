<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecretaryMedecin extends Model
{
    use HasFactory;

    protected $table = 'secretary_medecin';

    protected $fillable = [
        'secretary_id',
        'medecin_id',
        'statut',
        'date_demande',
        'date_decision',
        'motif_refus',
    ];

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }
}
