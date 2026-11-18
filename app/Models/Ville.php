<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $table = 'ville';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = ['id'];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'id_departement', 'id');
    }

    public function praticiens()
    {
        return $this->hasMany(Praticien::class, 'id_ville', 'id');
    }
}
