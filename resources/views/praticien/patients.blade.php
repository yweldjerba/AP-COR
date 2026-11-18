@extends('layouts.app')

@section('title', 'Mes patients - GSB')

@section('content')

<div class="praticien-page">

    <section class="praticien-hero">
        <div class="praticien-hero-text">
            <span class="eyebrow">Espace praticien</span>
            <h1>Mes patients</h1>
            <p>Tous les patients ayant déjà eu un rendez-vous avec vous, avec leurs coordonnées.</p>
        </div>
    </section>

    <section class="praticien-rdv-card">
        <div class="praticien-rdv-header">
            <div>
                <h3>Liste des patients</h3>
                <p>Rendez-vous acceptés ou terminés.</p>
            </div>
            <div class="rdv-total-badge">{{ $patients->count() }} {{ $patients->count() > 1 ? 'patients' : 'patient' }}</div>
        </div>

        <div class="praticien-rdv-body">
            @if($patients->isEmpty())
                <div class="praticien-empty">
                    <div class="praticien-empty-icon">👥</div>
                    <h3>Aucun patient</h3>
                    <p>Vos patients apparaîtront ici dès que vous aurez accepté un rendez-vous.</p>
                </div>
            @else
                <div class="patients-search">
                    <label for="filtrePatients" class="visually-hidden">Rechercher un patient</label>
                    <input type="search" id="filtrePatients" class="form-control" placeholder="Rechercher un patient par nom...">
                </div>

                <div class="table-wrapper">
                    <table class="table praticien-table" id="tablePatients">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Rendez-vous</th>
                                <th>Dernier RDV</th>
                                <th>Dossier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                                @php
                                    $nom = trim(($patient->prenom ?? '') . ' ' . ($patient->nom ?? ''));
                                    $entrees = $dossiers->get($patient->id, collect());
                                @endphp
                                <tr data-nom="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($nom . ' ' . $patient->nom)) }}">
                                    <td>
                                        <div class="patient-cell">
                                            <div class="patient-avatar">{{ strtoupper(substr($nom ?: 'P', 0, 1)) }}</div>
                                            <div class="patient-info"><strong>{{ $nom }}</strong></div>
                                        </div>
                                    </td>
                                    <td><a class="lien-contact" href="mailto:{{ $patient->email }}">{{ $patient->email }}</a></td>
                                    <td><a class="lien-contact" href="tel:{{ $patient->telephone }}">{{ $patient->telephone }}</a></td>
                                    <td>{{ $patient->nb_rdv }}</td>
                                    <td>{{ $patient->dernier_rdv ? \Carbon\Carbon::parse($patient->dernier_rdv)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($entrees->isEmpty())
                                            <span class="texte-discret">Vide</span>
                                        @else
                                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('dossier-{{ $patient->id }}').showModal()">
                                                Voir ({{ $entrees->count() }})
                                            </button>

                                            <dialog id="dossier-{{ $patient->id }}" class="rdv-detail-dialog">
                                                <h2>Dossier de {{ $nom }}</h2>
                                                <ul class="dossier-liste">
                                                    @foreach($entrees as $entree)
                                                        <li>
                                                            <strong>{{ \Carbon\Carbon::parse($entree->date_consultation)->format('d/m/Y') }}</strong>
                                                            <p>{{ $entree->symptome }}</p>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <form method="dialog"><button class="btn btn-primary">Fermer</button></form>
                                            </dialog>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</div>

<style>
    .rdv-detail-dialog { width: min(90vw, 520px); max-height: 85vh; border: 0; border-radius: 14px; padding: 24px; box-shadow: 0 16px 50px #0003; }
    .rdv-detail-dialog::backdrop { background: #0008; }
</style>

<script>
(() => {
    const champ = document.getElementById('filtrePatients');
    if (!champ) return;

    const normaliser = t => t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    champ.addEventListener('input', () => {
        const recherche = normaliser(champ.value.trim());
        document.querySelectorAll('#tablePatients tbody tr').forEach(ligne => {
            const mots = ligne.dataset.nom.split(' ');
            ligne.hidden = recherche !== '' && !mots.some(mot => mot.startsWith(recherche)) && !ligne.dataset.nom.startsWith(recherche);
        });
    });
})();
</script>

@endsection
