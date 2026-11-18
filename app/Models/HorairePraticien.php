<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Plage horaire d'un praticien pour un jour (1 = lundi ... 6 = samedi). */
class HorairePraticien extends Model
{
    protected $table = 'horaire_praticien';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_praticien',
        'jour',
        'actif',
        'heure_debut',
        'heure_fin',
    ];

    protected $casts = [
        'jour'  => 'integer',
        'actif' => 'boolean',
    ];

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id');
    }
}
