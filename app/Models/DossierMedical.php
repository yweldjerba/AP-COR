<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Une ligne = une consultation terminée (date + symptôme). */
class DossierMedical extends Model
{
    protected $table = 'dossier_medical';

    protected $primaryKey = 'id';

    // created_at est rempli par MySQL
    public $timestamps = false;

    protected $fillable = [
        'id_client',
        'id_praticien',
        'id_rendez_vous',
        'date_consultation',
        'symptome',
    ];

    protected $casts = [
        'date_consultation' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id');
    }

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class, 'id_rendez_vous', 'id');
    }
}
