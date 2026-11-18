<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'client';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'login',
        'mdp',
        'role',
        'id_region',
        'id_departement',
        'id_ville',
    ];

    protected $hidden = [
        'mdp',
    ];

    /* ---------- Relations ---------- */

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_client', 'id');
    }

    public function dossierMedical()
    {
        return $this->hasMany(DossierMedical::class, 'id_client', 'id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'id_ville', 'id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'id_departement', 'id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'id_region', 'id');
    }

    /* ---------- Raccourcis ---------- */

    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }
}
