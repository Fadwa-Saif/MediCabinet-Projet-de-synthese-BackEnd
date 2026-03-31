<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentreRadioAnalyse extends Model
{
    use HasFactory;

    protected $table = 'centres_radio_analyse';

    protected $fillable = [
        'nom',
        'type',
        'adresse',
        'telephone',
        'email',
        'ville',
    ];

    public function analyses(): HasMany
    {
        return $this->hasMany(Analyse::class, 'centre_id');
    }

    public function radiologies(): HasMany
    {
        return $this->hasMany(Radiologie::class, 'centre_id');
    }
}
