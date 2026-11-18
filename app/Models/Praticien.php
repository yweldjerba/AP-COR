<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Praticien extends Model
{
    protected $table = 'praticien';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = ['id'];

    /* ---------- Relations ---------- */

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_praticien', 'id');
    }

    /** Comptes de connexion (table utilisateur, colonne id_praticien). */
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'id_praticien', 'id');
    }

    public function horaires()
    {
        return $this->hasMany(HorairePraticien::class, 'id_praticien', 'id');
    }

    public function dossiers()
    {
        return $this->hasMany(DossierMedical::class, 'id_praticien', 'id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'id_ville', 'id');
    }

    /* ---------- Raccourcis ---------- */

    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }
}
