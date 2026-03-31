<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medicament extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'nom_generique',
        'forme',
        'description',
    ];

    public function ordonnances(): BelongsToMany
    {
        return $this->belongsToMany(Ordonnance::class, 'prescription')
            ->withPivot('posologie', 'observation', 'quantite', 'duree_traitement');
    }
}
