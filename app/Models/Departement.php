<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $table = 'departement';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = ['id'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'id_region', 'id');
    }

    public function villes()
    {
        return $this->hasMany(Ville::class, 'id_departement', 'id');
    }
}
