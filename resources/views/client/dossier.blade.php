@extends('layouts.app')

@section('title', 'Mon dossier médical - GSB')

@section('content')

<div class="client-page">

    <div class="client-header">
        <div>
            <span class="eyebrow">Espace client</span>
            <h1>Mon dossier médical</h1>
            <p>Les observations saisies par vos praticiens à la fin de chaque consultation.</p>
        </div>
    </div>

    <div class="rdv-card">
        <div class="rdv-card-header">
            <div>
                <h3>Consultations</h3>
                <p>De la plus récente à la plus ancienne.</p>
            </div>
            <span class="total-count">{{ $entrees->count() }} {{ $entrees->count() > 1 ? 'entrées' : 'entrée' }}</span>
        </div>

        <div class="rdv-card-body">
            @if($entrees->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">🩺</div>
                    <h3>Dossier vide</h3>
                    <p>Votre dossier sera complété par le praticien après votre premier rendez-vous.</p>
                    <a href="{{ route('client.accueil') }}" class="btn btn-primary">Voir mes rendez-vous</a>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="rdv-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Praticien</th>
                                <th>Symptôme / observation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entrees as $entree)
                                <tr>
                                    <td>
                                        <strong>{{ \Carbon\Carbon::parse($entree->date_consultation)->format('d/m/Y') }}</strong>
                                    </td>
                                    <td>
                                        {{ trim(($entree->praticien_prenom ?? '') . ' ' . ($entree->praticien_nom ?? '')) ?: 'Praticien inconnu' }}
                                    </td>
                                    <td class="dossier-symptome">{{ $entree->symptome }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
