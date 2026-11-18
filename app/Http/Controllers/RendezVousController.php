<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CRÉER UN RENDEZ-VOUS
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
         * Vérifier que l'utilisateur connecté
         * est bien un client.
         */
        if (session('user_role') !== 'client') {
            return redirect()->route('login');
        }

        /*
         * Vérifier que l'id du client existe
         * dans la session.
         */
        if (!session('id_client')) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Votre session a expiré. Veuillez vous reconnecter.'
                );
        }

        /*
         * Validation du formulaire.
         */
        $request->validate(
            [
                'id_praticien' => [
                    'required',
                    'integer',
                    'exists:praticien,id',
                ],

                'date_rdv' => [
                    'required',
                    'date',
                    'after_or_equal:today',
                ],

                'heure_rdv' => [
                    'required',
                ],

                'motif' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ],
            [
                'id_praticien.required' =>
                    'Veuillez choisir un praticien.',

                'id_praticien.exists' =>
                    'Le praticien sélectionné est invalide.',

                'date_rdv.required' =>
                    'Veuillez choisir une date.',

                'date_rdv.date' =>
                    'La date sélectionnée est invalide.',

                'date_rdv.after_or_equal' =>
                    'La date du rendez-vous ne peut pas être passée.',

                'heure_rdv.required' =>
                    'Veuillez choisir une heure.',

                'motif.required' =>
                    'Veuillez indiquer le motif du rendez-vous.',

                'motif.max' =>
                    'Le motif ne doit pas dépasser 255 caractères.',
            ]
        );

        /*
         * Vérifier que le créneau n'est pas
         * déjà demandé ou accepté.
         */
        $dejaPris = RendezVous::where(
            'id_praticien',
            $request->id_praticien
        )
            ->where(
                'date_rdv',
                $request->date_rdv
            )
            ->where(
                'heure_rdv',
                $request->heure_rdv
            )
            ->whereIn(
                'statut',
                [
                    'en_attente',
                    'accepte',
                ]
            )
            ->exists();

        /*
         * Créneau indisponible.
         */
        if ($dejaPris) {
            return back()
                ->withInput()
                ->withErrors([
                    'heure_rdv' =>
                        'Ce créneau est déjà demandé ou réservé.',
                ]);
        }

        /*
         * Création du rendez-vous.
         */
        RendezVous::create([
            'id_client' =>
                session('id_client'),

            'id_praticien' =>
                $request->id_praticien,

            'date_rdv' =>
                $request->date_rdv,

            'heure_rdv' =>
                $request->heure_rdv,

            'motif' =>
                $request->motif,

            'statut' =>
                'en_attente',
        ]);

        /*
         * Retour vers l'espace client.
         */
        return redirect()
            ->route('client.accueil')
            ->with(
                'success',
                'Votre demande de rendez-vous a bien été envoyée.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACCEPTER UN RENDEZ-VOUS
    |--------------------------------------------------------------------------
    */

    public function accepter($id)
    {
        /*
         * Vérifier le rôle.
         */
        if (session('user_role') !== 'praticien') {
            return redirect()->route('login');
        }

        /*
         * Vérifier l'id praticien.
         */
        if (!session('id_prat')) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Votre session a expiré. Veuillez vous reconnecter.'
                );
        }

        /*
         * Chercher le rendez-vous.
         */
        $rendezVous = RendezVous::where(
            'id',
            $id
        )
            ->where(
                'id_praticien',
                session('id_prat')
            )
            ->firstOrFail();

        /*
         * Vérifier son statut.
         */
        if ($rendezVous->statut !== 'en_attente') {
            return redirect()
                ->route('praticien.accueil')
                ->with(
                    'error',
                    'Ce rendez-vous a déjà été traité.'
                );
        }

        /*
         * Accepter.
         */
        $rendezVous->statut = 'accepte';

        $rendezVous->save();

        return redirect()
            ->route('praticien.accueil')
            ->with(
                'success',
                'Le rendez-vous a été accepté.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | REFUSER UN RENDEZ-VOUS
    |--------------------------------------------------------------------------
    */

    public function refuser($id)
    {
        /*
         * Vérifier le rôle.
         */
        if (session('user_role') !== 'praticien') {
            return redirect()->route('login');
        }

        /*
         * Vérifier l'id praticien.
         */
        if (!session('id_prat')) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Votre session a expiré. Veuillez vous reconnecter.'
                );
        }

        /*
         * Chercher le rendez-vous.
         */
        $rendezVous = RendezVous::where(
            'id',
            $id
        )
            ->where(
                'id_praticien',
                session('id_prat')
            )
            ->firstOrFail();

        /*
         * Vérifier son statut.
         */
        if ($rendezVous->statut !== 'en_attente') {
            return redirect()
                ->route('praticien.accueil')
                ->with(
                    'error',
                    'Ce rendez-vous a déjà été traité.'
                );
        }

        /*
         * Refuser.
         */
        $rendezVous->statut = 'refuse';

        $rendezVous->save();

        return redirect()
            ->route('praticien.accueil')
            ->with(
                'success',
                'Le rendez-vous a été refusé.'
            );
    }
}