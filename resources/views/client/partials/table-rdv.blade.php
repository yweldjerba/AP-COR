{{-- Tableau de rendez-vous côté client. Variables : $liste, $vide --}}
@php
    $classesStatut = [
        'en_attente' => 'status-waiting',
        'accepte'    => 'status-accepted',
        'refuse'     => 'status-refused',
        'termine'    => 'status-done',
    ];
@endphp

@if($liste->isEmpty())

    <div class="empty-state empty-state-compact">
        <p>{{ $vide }}</p>
    </div>

@else

    <div class="table-wrapper">
        <table class="rdv-table">
            <thead>
                <tr>
                    <th>Praticien</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                @foreach($liste as $rdv)
                    @php
                        $nomPraticien = trim(($rdv->praticien->prenom ?? '') . ' ' . ($rdv->praticien->nom ?? '')) ?: 'Praticien inconnu';
                        $date = $rdv->date_rdv ? $rdv->date_rdv->format('d/m/Y') : '-';
                        $heure = $rdv->heure_rdv ? substr($rdv->heure_rdv, 0, 5) : '-';
                        $libelle = \App\Support\Gsb::STATUTS[$rdv->statut] ?? $rdv->statut;
                    @endphp
                    <tr>
                        <td>
                            <div class="person-cell">
                                <div class="person-avatar">{{ strtoupper(substr($nomPraticien, 0, 1)) }}</div>
                                <div class="person-info">
                                    <strong>{{ $nomPraticien }}</strong>
                                    <span>Praticien</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $date }}</td>
                        <td><span class="rdv-time">{{ $heure }}</span></td>
                        <td class="motif-cell">{{ $rdv->motif ?: '-' }}</td>
                        <td>
                            <span class="status {{ $classesStatut[$rdv->statut] ?? '' }}">{{ $libelle }}</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('detail-rdv-{{ $rdv->id }}').showModal()">
                                Détails
                            </button>

                            <dialog id="detail-rdv-{{ $rdv->id }}" class="rdv-detail-dialog" aria-labelledby="detail-title-{{ $rdv->id }}">
                                <h2 id="detail-title-{{ $rdv->id }}">Rendez-vous du {{ $date }}</h2>
                                <dl>
                                    <dt>Praticien</dt><dd>{{ $nomPraticien }}</dd>
                                    <dt>Date</dt><dd>{{ $date }}</dd>
                                    <dt>Heure</dt><dd>{{ $heure }}</dd>
                                    <dt>Statut</dt><dd>{{ $libelle }}</dd>
                                    <dt>Motif</dt><dd class="rdv-detail-motif">{{ $rdv->motif ?: 'Non renseigné' }}</dd>
                                </dl>
                                <form method="dialog">
                                    <button class="btn btn-primary">Fermer</button>
                                </form>
                            </dialog>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endif
