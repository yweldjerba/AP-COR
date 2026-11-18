<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Medicament;
use Illuminate\Support\Facades\DB;

class PanierService
{
    public function expirerCommandes(): int
    {
        $commandes = Commande::with('lignes')
            ->where('statut', 'en_cours')
            ->whereNotNull('date_expiration')
            ->where('date_expiration', '<=', now())
            ->get();

        $compteur = 0;

        foreach ($commandes as $commande) {
            DB::transaction(function () use ($commande) {
                $commandeVerrouillee = Commande::where('id_commande', $commande->id_commande)
                    ->lockForUpdate()
                    ->first();

                if (!$commandeVerrouillee || $commandeVerrouillee->statut !== 'en_cours') {
                    return;
                }

                if (!$commandeVerrouillee->date_expiration || $commandeVerrouillee->date_expiration->isFuture()) {
                    return;
                }

                foreach ($commandeVerrouillee->lignes()->get() as $ligne) {
                    Medicament::where('id', $ligne->id_medicament)
                        ->increment('stock', $ligne->quantite);
                }

                $commandeVerrouillee->statut = 'expiree';
                $commandeVerrouillee->save();
            });

            $compteur++;
        }

        return $compteur;
    }

    public function panierActifPour(int $idPraticien): ?Commande
    {
        $this->expirerCommandes();

        return Commande::with('lignes.medicament')
            ->where('id_praticien', $idPraticien)
            ->where('statut', 'en_cours')
            ->orderByDesc('id_commande')
            ->first();
    }
}
