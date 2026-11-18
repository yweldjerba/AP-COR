<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $primaryKey = 'id';

    // created_at est rempli par MySQL (DEFAULT CURRENT_TIMESTAMP)
    // date_heure est calculée par MySQL : ne jamais l'écrire
    public $timestamps = false;

    protected $fillable = [
        'id_client',
        'id_praticien',
        'date_rdv',
        'heure_rdv',
        'motif',
        'statut',
    ];

    protected $casts = [
        'date_rdv' => 'date',
    ];

    /* ---------- Relations ---------- */

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id');
    }

    public function dossier()
    {
        return $this->hasOne(DossierMedical::class, 'id_rendez_vous', 'id');
    }

    /* ---------- Méthodes utilisées par les contrôleurs ---------- */

    /** Date + heure du rendez-vous. */
    public function debut(): ?Carbon
    {
        if (!$this->date_rdv) {
            return null;
        }

        return Carbon::parse(
            $this->date_rdv->format('Y-m-d') . ' ' . ($this->heure_rdv ?: '00:00:00')
        );
    }

    /** En attente ou accepté, et pas encore passé. */
    public function estAVenir(): bool
    {
        $debut = $this->debut();

        return in_array($this->statut, ['en_attente', 'accepte'], true)
            && $debut !== null
            && $debut->gte(now());
    }
}
