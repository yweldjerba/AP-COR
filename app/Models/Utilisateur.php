<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Comptes de connexion des praticiens. */
class Utilisateur extends Model
{
    protected $table = 'utilisateur';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'login',
        'mdp',
        'id_praticien',
    ];

    protected $hidden = [
        'mdp',
        'remember_token',
    ];

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id');
    }
}
