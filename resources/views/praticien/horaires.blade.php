@extends('layouts.app')

@section('title', 'Mes horaires - GSB')

@section('content')

<div class="praticien-page">

    <section class="praticien-hero">
        <div class="praticien-hero-text">
            <span class="eyebrow">Espace praticien</span>
            <h1>Mes plages horaires</h1>
            <p>Par défaut, vous consultez de 8h à 18h du lundi au vendredi. Le samedi est facultatif.</p>
        </div>
    </section>

    <section class="praticien-rdv-card">
        <div class="praticien-rdv-header">
            <div>
                <h3>Jours et heures de consultation</h3>
                <p>Les patients ne pourront réserver que sur ces plages, par créneaux de {{ \App\Support\Gsb::DUREE_CRENEAU }} minutes.</p>
            </div>
        </div>

        <div class="praticien-rdv-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $erreur)
                        <div>{{ $erreur }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('praticien.horaires.update') }}">
                @csrf

                <div class="table-wrapper">
                    <table class="table praticien-table horaires-table">
                        <thead>
                            <tr>
                                <th>Jour</th>
                                <th>Je consulte</th>
                                <th>Début</th>
                                <th>Fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($horaires as $numero => $jour)
                                @php
                                    $actif = old("jours.$numero") !== null
                                        ? !empty(old("jours.$numero.actif"))
                                        : $jour['actif'];
                                @endphp
                                <tr class="horaire-ligne {{ $actif ? '' : 'inactif' }}">
                                    <td><strong>{{ $jour['nom'] }}</strong></td>
                                    <td>
                                        <label class="interrupteur">
                                            <input type="checkbox" name="jours[{{ $numero }}][actif]" value="1" @checked($actif) onchange="this.closest('tr').classList.toggle('inactif', !this.checked)">
                                            <span>{{ $numero === 6 ? 'Travailler le samedi' : 'Oui' }}</span>
                                        </label>
                                    </td>
                                    <td>
                                        <input type="time" name="jours[{{ $numero }}][debut]" class="form-control" value="{{ old("jours.$numero.debut", $jour['debut']) }}" step="1800" required>
                                    </td>
                                    <td>
                                        <input type="time" name="jours[{{ $numero }}][fin]" class="form-control" value="{{ old("jours.$numero.fin", $jour['fin']) }}" step="1800" required>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="horaires-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer mes horaires</button>
                </div>
            </form>

            <p class="texte-discret horaires-note">
                Modifier vos horaires ne supprime pas les rendez-vous déjà pris.
            </p>
        </div>
    </section>
</div>

@endsection
