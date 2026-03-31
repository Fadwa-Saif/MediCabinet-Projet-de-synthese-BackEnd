<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilite extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'jour_semaine',
        'heure_debut',
        'heure_fin',
        'duree_min',
        'est_disponible',
        'date_exception',
    ];

    protected function casts(): array
    {
        return [
            'est_disponible' => 'boolean',
            'date_exception' => 'date',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function getCreneaux(): array
    {
        $creneaux = [];

        if (empty($this->heure_debut) || empty($this->heure_fin) || empty($this->duree_min) || $this->duree_min <= 0) {
            return $creneaux;
        }

        $debut = Carbon::createFromFormat('H:i:s', $this->heure_debut);
        $fin = Carbon::createFromFormat('H:i:s', $this->heure_fin);

        while ($debut < $fin) {
            $creneaux[] = $debut->format('H:i');
            $debut->addMinutes((int) $this->duree_min);
        }

        return $creneaux;
    }
}
