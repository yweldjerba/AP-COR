<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Règles métier partagées par les contrôleurs.
 */
class Gsb
{
    /** Région dont les praticiens sont proposés (colonne region.slug). */
    public const SLUG_REGION = 'corse';

    /** Durée d'un créneau de rendez-vous, en minutes. */
    public const DUREE_CRENEAU = 30;

    /** Jours gérés : 1 = lundi ... 6 = samedi. */
    public const JOURS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    ];

    public const STATUTS = [
        'en_attente' => 'En attente',
        'accepte'    => 'Accepté',
        'refuse'     => 'Refusé',
        'termine'    => 'Terminé',
    ];

    /** Statuts qui occupent un créneau. */
    public const STATUTS_OCCUPANT = ['en_attente', 'accepte', 'termine'];

    public static function regionId(): ?int
    {
        $id = DB::table('region')
            ->where('slug', self::SLUG_REGION)
            ->value('id');

        return $id ? (int) $id : null;
    }

    public static function praticienDansRegion($idPraticien): bool
    {
        return DB::table('praticien')
            ->join('ville', 'ville.id', '=', 'praticien.id_ville')
            ->join('departement', 'departement.id', '=', 'ville.id_departement')
            ->join('region', 'region.id', '=', 'departement.id_region')
            ->where('praticien.id', $idPraticien)
            ->where('region.slug', self::SLUG_REGION)
            ->exists();
    }

    /** Par défaut : 8h-18h du lundi au vendredi, samedi non travaillé. */
    public static function horairesParDefaut(): array
    {
        $horaires = [];

        foreach (self::JOURS as $numero => $nom) {
            $horaires[$numero] = [
                'nom'   => $nom,
                'actif' => $numero <= 5,
                'debut' => '08:00',
                'fin'   => '18:00',
            ];
        }

        return $horaires;
    }

    /** Horaires réels d'un praticien (valeurs par défaut + ses modifications). */
    public static function horaires(int $idPraticien): array
    {
        $horaires = self::horairesParDefaut();

        $lignes = DB::table('horaire_praticien')
            ->where('id_praticien', $idPraticien)
            ->get();

        foreach ($lignes as $ligne) {
            if (!isset($horaires[$ligne->jour])) {
                continue;
            }

            $horaires[$ligne->jour]['actif'] = (bool) $ligne->actif;
            $horaires[$ligne->jour]['debut'] = substr($ligne->heure_debut, 0, 5);
            $horaires[$ligne->jour]['fin'] = substr($ligne->heure_fin, 0, 5);
        }

        return $horaires;
    }

    /** Noms des jours où le praticien consulte. */
    public static function joursTravailles(int $idPraticien): array
    {
        return collect(self::horaires($idPraticien))
            ->filter(fn ($jour) => $jour['actif'])
            ->map(fn ($jour) => $jour['nom'] . ' ' . $jour['debut'] . '-' . $jour['fin'])
            ->values()
            ->all();
    }

    /**
     * Créneaux libres d'un praticien pour une date (format Y-m-d).
     * Retourne une liste d'heures "HH:MM".
     */
    public static function creneauxDisponibles(int $idPraticien, string $date): array
    {
        try {
            $jour = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable $e) {
            return [];
        }

        if ($jour->lt(today())) {
            return [];
        }

        $horaire = self::horaires($idPraticien)[$jour->dayOfWeekIso] ?? null;

        if (!$horaire || !$horaire['actif']) {
            return [];
        }

        $dejaPris = DB::table('rendez_vous')
            ->where('id_praticien', $idPraticien)
            ->whereDate('date_rdv', $date)
            ->whereIn('statut', self::STATUTS_OCCUPANT)
            ->pluck('heure_rdv')
            ->map(fn ($heure) => substr($heure, 0, 5))
            ->all();

        $debut = Carbon::parse($date . ' ' . $horaire['debut']);
        $fin = Carbon::parse($date . ' ' . $horaire['fin']);
        $maintenant = now();

        $creneaux = [];

        for (
            $heure = $debut->copy();
            $heure->copy()->addMinutes(self::DUREE_CRENEAU)->lte($fin);
            $heure->addMinutes(self::DUREE_CRENEAU)
        ) {
            $texte = $heure->format('H:i');

            if (in_array($texte, $dejaPris, true)) {
                continue;
            }

            if ($heure->lte($maintenant)) {
                continue;
            }

            $creneaux[] = $texte;
        }

        return $creneaux;
    }
}
