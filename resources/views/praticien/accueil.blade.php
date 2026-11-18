@extends('layouts.app')

@section('title', 'Mes rendez-vous - GSB')

@section('content')

@php
    $totalRdv = $rendezVous->count();
    $enAttente = $rendezVous->where('statut', 'en_attente')->count();
    $acceptes = $rendezVous->where('statut', 'accepte')->count();
    $termines = $rendezVous->where('statut', 'termine')->count();

    $classesStatut = [
        'en_attente' => 'rdv-status-waiting',
        'accepte'    => 'rdv-status-accepted',
        'refuse'     => 'rdv-status-refused',
        'termine'    => 'rdv-status-done',
    ];
@endphp

<div class="praticien-page">

    <section class="praticien-hero">
        <div class="praticien-hero-text">
            <span class="eyebrow">Espace praticien</span>
            <h1>Bonjour {{ session('user_prenom') }} {{ session('user_nom') ?? 'Praticien' }}</h1>
            <p>Traitez les demandes, consultez votre calendrier et complétez les dossiers médicaux.</p>
        </div>
    </section>

    <section class="rdv-dashboard-stats">
        <div class="dashboard-stat-card">
            <div class="dashboard-stat-content">
                <span class="dashboard-stat-label">Rendez-vous</span>
                <div class="dashboard-stat-number">{{ $totalRdv }}</div>
                <span class="dashboard-stat-description">{{ $totalRdv > 1 ? 'demandes au total' : 'demande au total' }}</span>
            </div>
        </div>
        <div class="dashboard-stat-card">
            <div class="dashboard-stat-content">
                <span class="dashboard-stat-label">En attente</span>
                <div class="dashboard-stat-number">{{ $enAttente }}</div>
                <span class="dashboard-stat-description">À traiter</span>
            </div>
        </div>
        <div class="dashboard-stat-card">
            <div class="dashboard-stat-content">
                <span class="dashboard-stat-label">Acceptés</span>
                <div class="dashboard-stat-number">{{ $acceptes }}</div>
                <span class="dashboard-stat-description">À réaliser</span>
            </div>
        </div>
        <div class="dashboard-stat-card">
            <div class="dashboard-stat-content">
                <span class="dashboard-stat-label">Terminés</span>
                <div class="dashboard-stat-number">{{ $termines }}</div>
                <span class="dashboard-stat-description">Dossiers complétés</span>
            </div>
        </div>
    </section>

    <section class="planning-heading">
        <div>
            <span class="eyebrow">Planning</span>
            <h2>Mes rendez-vous</h2>
            <p>Toutes les demandes de rendez-vous qui vous sont adressées.</p>
        </div>
        <div class="planning-actions">
            @if($enAttente > 0)
                <div class="planning-notification">
                    {{ $enAttente }} {{ $enAttente > 1 ? 'demandes à traiter' : 'demande à traiter' }}
                </div>
            @endif
            <button type="button" id="open-calendar-modal" class="btn-calendar" aria-haspopup="dialog" aria-controls="calendar-modal">Calendrier</button>
        </div>
    </section>

    <section class="praticien-rdv-card">
        <div class="praticien-rdv-header">
            <div>
                <h3>Demandes de rendez-vous</h3>
                <p>Acceptez ou refusez les demandes, puis terminez le rendez-vous en saisissant le dossier médical.</p>
            </div>
            <div class="rdv-total-badge">{{ $totalRdv }} rendez-vous</div>
        </div>

        <div class="praticien-rdv-body">
            @if($rendezVous->isEmpty())
                <div class="praticien-empty">
                    <div class="praticien-empty-icon">📅</div>
                    <h3>Aucun rendez-vous</h3>
                    <p>Vous n'avez aucune demande de rendez-vous pour le moment.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="table praticien-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Motif</th>
                                <th>Statut</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rendezVous as $rdv)
                                @php
                                    $nomPatient = trim(($rdv->client->prenom ?? '') . ' ' . ($rdv->client->nom ?? '')) ?: 'Patient inconnu';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="patient-cell">
                                            <div class="patient-avatar">{{ strtoupper(substr($nomPatient, 0, 1)) }}</div>
                                            <div class="patient-info">
                                                <strong>{{ $nomPatient }}</strong>
                                                <span>{{ $rdv->client->telephone ?? 'Patient' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rdv-date-cell">
                                            <strong>{{ $rdv->date_rdv ? $rdv->date_rdv->format('d/m/Y') : '-' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rdv-time">{{ $rdv->heure_rdv ? substr($rdv->heure_rdv, 0, 5) : '-' }}</span>
                                    </td>
                                    <td class="motif-cell">{{ $rdv->motif ?: '-' }}</td>
                                    <td>
                                        <span class="rdv-status {{ $classesStatut[$rdv->statut] ?? '' }}">
                                            {{ \App\Support\Gsb::STATUTS[$rdv->statut] ?? $rdv->statut }}
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        @if($rdv->statut === 'en_attente')
                                            <div class="rdv-actions">
                                                <form method="POST" action="{{ route('praticien.rendez-vous.accepter', $rdv->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-action-rdv btn-accepter">✓ Accepter</button>
                                                </form>
                                                <form method="POST" action="{{ route('praticien.rendez-vous.refuser', $rdv->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-action-rdv btn-refuser">✕ Refuser</button>
                                                </form>
                                            </div>
                                        @elseif($rdv->statut === 'accepte')
                                            <button
                                                type="button"
                                                class="btn-action-rdv btn-terminer"
                                                data-id="{{ $rdv->id }}"
                                                data-patient="{{ $nomPatient }}"
                                                data-date="{{ $rdv->date_rdv ? $rdv->date_rdv->format('Y-m-d') : '' }}"
                                                onclick="ouvrirTerminer(this.dataset)"
                                            >
                                                Terminer
                                            </button>
                                        @elseif($rdv->statut === 'termine')
                                            <span class="action-done action-done-termine">✓ Dossier saisi</span>
                                        @elseif($rdv->statut === 'refuse')
                                            <span class="action-done action-refused">✕ Refusé</span>
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


{{-- =========================================================
     MODALE : TERMINER LE RDV + DOSSIER MÉDICAL
     ========================================================= --}}

<div id="modalTerminer" class="modal-overlay {{ $errors->has('symptome') || $errors->has('date_consultation') ? 'active' : '' }}">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="eyebrow">Fin du rendez-vous</span>
                <h2>Dossier médical</h2>
                <p id="terminerPatient">Saisissez le symptôme constaté pendant la consultation.</p>
            </div>
            <button type="button" class="modal-close" onclick="fermerTerminer()" aria-label="Fermer">×</button>
        </div>

        @if($errors->has('symptome') || $errors->has('date_consultation'))
            <div class="form-errors">
                @foreach(['date_consultation', 'symptome'] as $champ)
                    @error($champ) <p>{{ $message }}</p> @enderror
                @endforeach
            </div>
        @endif

        <form id="formTerminer" method="POST" action="">
            @csrf
            <input type="hidden" id="terminer_rdv_id" name="rdv_id" value="{{ old('rdv_id') }}">

            <div class="modal-body">
                <div class="form-group">
                    <label for="date_consultation">Date de la consultation</label>
                    <input type="date" id="date_consultation" name="date_consultation" class="form-control" value="{{ old('date_consultation') }}" max="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="symptome">Symptôme / observation</label>
                    <textarea id="symptome" name="symptome" class="form-control" rows="5" maxlength="1000" placeholder="Ex. : fièvre depuis 3 jours, toux sèche..." required>{{ old('symptome') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fermerTerminer()">Annuler</button>
                <button type="submit" class="btn btn-primary">Terminer et enregistrer</button>
            </div>
        </form>
    </div>
</div>


{{-- =========================================================
     MODALE : CALENDRIER
     ========================================================= --}}

<div id="calendar-modal" class="calendar-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="calendar-modal-title" aria-hidden="true">
    <div class="calendar-modal" role="document">
        <div class="calendar-modal-header">
            <h2 id="calendar-modal-title">Calendrier des rendez-vous</h2>
            <button type="button" id="close-calendar-modal" class="calendar-modal-close" aria-label="Fermer le calendrier">×</button>
        </div>
        <div class="calendar-modal-body">
            <section class="praticien-calendar" aria-label="Calendrier des rendez-vous">
                <div class="calendar-heading">
                    <strong id="calendar-month" aria-live="polite"></strong>
                    <div class="calendar-navigation">
                        <button type="button" id="calendar-prev" aria-label="Mois précédent">‹</button>
                        <button type="button" id="calendar-today">Aujourd'hui</button>
                        <button type="button" id="calendar-next" aria-label="Mois suivant">›</button>
                    </div>
                </div>
                <div class="calendar-weekdays" aria-hidden="true">
                    <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
                </div>
                <div id="calendar-days" class="calendar-days"></div>
                <div class="calendar-legend" aria-label="Légende du calendrier">
                    <span><i class="calendar-legend-dot waiting"></i>En attente</span>
                    <span><i class="calendar-legend-dot accepted"></i>Accepté</span>
                    <span><i class="calendar-legend-dot done"></i>Terminé</span>
                    <span><i class="calendar-legend-dot refused"></i>Refusé</span>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="appointment-detail" class="appointment-detail-overlay" role="dialog" aria-modal="true" aria-labelledby="appointment-detail-title" aria-hidden="true">
    <div class="appointment-detail-card">
        <div class="appointment-detail-header">
            <div>
                <span class="eyebrow">Rendez-vous</span>
                <h3 id="appointment-detail-title">Détails</h3>
            </div>
            <button type="button" id="close-appointment-detail" class="appointment-detail-close" aria-label="Fermer">×</button>
        </div>
        <div class="appointment-detail-body">
            <div class="appointment-detail-patient">
                <div id="appointment-detail-avatar" class="appointment-detail-avatar"></div>
                <div>
                    <span>Patient</span>
                    <strong id="appointment-detail-patient"></strong>
                </div>
            </div>
            <div class="appointment-detail-grid">
                <div><span>Date</span><strong id="appointment-detail-date"></strong></div>
                <div><span>Heure</span><strong id="appointment-detail-time"></strong></div>
            </div>
            <div class="appointment-detail-line">
                <span>Motif</span>
                <p id="appointment-detail-reason"></p>
            </div>
            <div class="appointment-detail-line">
                <span>Statut</span>
                <strong id="appointment-detail-status" class="appointment-detail-status"></strong>
            </div>
        </div>
        <div id="appointment-detail-actions" class="appointment-detail-actions">
            <form id="appointment-refuse-form" method="POST">
                @csrf
                <button type="submit" class="btn-action-rdv btn-refuser">Refuser</button>
            </form>
            <form id="appointment-accept-form" method="POST">
                @csrf
                <button type="submit" class="btn-action-rdv btn-accepter">Accepter</button>
            </form>
        </div>
    </div>
</div>

@php
    $appointments = $rendezVous
        ->filter(fn ($rdv) => !empty($rdv->date_rdv))
        ->map(fn ($rdv) => [
            'date' => $rdv->date_rdv->format('Y-m-d'),
            'time' => $rdv->heure_rdv ? substr($rdv->heure_rdv, 0, 5) : '',
            'name' => trim(($rdv->client?->prenom ?? '') . ' ' . ($rdv->client?->nom ?? '')) ?: 'Patient inconnu',
            'status' => $rdv->statut,
            'reason' => $rdv->motif ?: 'Motif non renseigné',
            'acceptUrl' => route('praticien.rendez-vous.accepter', $rdv->id),
            'refuseUrl' => route('praticien.rendez-vous.refuser', $rdv->id),
        ])
        ->values();
@endphp

<script>
/* ---------- Terminer un rendez-vous ---------- */

const modalTerminer = document.getElementById('modalTerminer');
const formTerminer = document.getElementById('formTerminer');
const urlTerminer = @json(route('praticien.rendez-vous.terminer', '__ID__'));

function ouvrirTerminer(donnees) {
    formTerminer.action = urlTerminer.replace('__ID__', donnees.id);
    document.getElementById('terminer_rdv_id').value = donnees.id;
    document.getElementById('terminerPatient').textContent = 'Patient : ' + donnees.patient;

    const maintenant = new Date();
    const aujourdhui = [
        maintenant.getFullYear(),
        String(maintenant.getMonth() + 1).padStart(2, '0'),
        String(maintenant.getDate()).padStart(2, '0'),
    ].join('-');
    document.getElementById('date_consultation').value =
        donnees.date && donnees.date <= aujourdhui ? donnees.date : aujourdhui;
    document.getElementById('symptome').value = '';

    modalTerminer.classList.add('active');
    document.body.classList.add('modal-open');
    document.getElementById('symptome').focus();
}

function fermerTerminer() {
    modalTerminer.classList.remove('active');
    document.body.classList.remove('modal-open');
}

modalTerminer.addEventListener('click', e => {
    if (e.target === modalTerminer) fermerTerminer();
});

// Après une erreur de validation : remettre la bonne adresse au formulaire
(() => {
    const ancienId = document.getElementById('terminer_rdv_id').value;
    if (ancienId) {
        formTerminer.action = urlTerminer.replace('__ID__', ancienId);
        if (modalTerminer.classList.contains('active')) document.body.classList.add('modal-open');
    }
})();


/* ---------- Calendrier ---------- */

(() => {
    const appointments = {{ Illuminate\Support\Js::from($appointments) }};
    const modal = document.getElementById('calendar-modal');
    const openModalButton = document.getElementById('open-calendar-modal');
    const closeModalButton = document.getElementById('close-calendar-modal');
    const detail = document.getElementById('appointment-detail');
    const closeDetailButton = document.getElementById('close-appointment-detail');
    const detailActions = document.getElementById('appointment-detail-actions');
    const acceptForm = document.getElementById('appointment-accept-form');
    const refuseForm = document.getElementById('appointment-refuse-form');
    const days = document.getElementById('calendar-days');
    const monthLabel = document.getElementById('calendar-month');
    const today = new Date();
    let shown = new Date(today.getFullYear(), today.getMonth(), 1);
    let lastEventButton = null;

    const labels = { en_attente: 'En attente', accepte: 'Accepté', refuse: 'Refusé', termine: 'Terminé' };
    const dateKey = (y, m, d) => `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const formatDate = value => {
        const [y, m, d] = value.split('-').map(Number);
        return new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(y, m - 1, d));
    };

    function openDetail(appointment, button) {
        lastEventButton = button;
        document.getElementById('appointment-detail-patient').textContent = appointment.name;
        document.getElementById('appointment-detail-avatar').textContent = appointment.name.trim().charAt(0).toUpperCase() || 'P';
        document.getElementById('appointment-detail-date').textContent = formatDate(appointment.date);
        document.getElementById('appointment-detail-time').textContent = appointment.time || 'Heure à définir';
        document.getElementById('appointment-detail-reason').textContent = appointment.reason;

        const status = document.getElementById('appointment-detail-status');
        status.textContent = labels[appointment.status] || appointment.status;
        status.className = `appointment-detail-status ${appointment.status}`;

        if (appointment.status === 'en_attente') {
            detailActions.hidden = false;
            acceptForm.action = appointment.acceptUrl;
            refuseForm.action = appointment.refuseUrl;
        } else {
            detailActions.hidden = true;
        }

        detail.classList.add('active');
        detail.setAttribute('aria-hidden', 'false');
        closeDetailButton.focus();
    }

    function closeDetail() {
        detail.classList.remove('active');
        detail.setAttribute('aria-hidden', 'true');
        if (lastEventButton) lastEventButton.focus();
    }

    function render() {
        const year = shown.getFullYear(), month = shown.getMonth();
        monthLabel.textContent = new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' }).format(shown);
        days.replaceChildren();

        const offset = (new Date(year, month, 1).getDay() + 6) % 7;
        const count = new Date(year, month + 1, 0).getDate();
        const todayKey = dateKey(today.getFullYear(), today.getMonth(), today.getDate());

        for (let slot = 0; slot < offset + count; slot++) {
            const day = slot - offset + 1;
            const cell = document.createElement('div');
            cell.className = 'calendar-day' + (day < 1 || day > count ? ' outside' : '');

            if (day >= 1 && day <= count) {
                const key = dateKey(year, month, day);
                if (key === todayKey) cell.classList.add('today');

                const number = document.createElement('span');
                number.className = 'calendar-date';
                number.textContent = day;
                cell.append(number);

                appointments
                    .filter(item => item.date === key)
                    .sort((a, b) => a.time.localeCompare(b.time))
                    .forEach(appointment => {
                        const event = document.createElement('button');
                        event.type = 'button';
                        event.className = 'calendar-event ' + (appointment.status !== 'en_attente' ? appointment.status : '');
                        event.textContent = `${appointment.time} ${appointment.name}`.trim();
                        event.setAttribute('aria-label', `Voir le rendez-vous de ${appointment.name}`);
                        event.addEventListener('click', () => openDetail(appointment, event));
                        cell.append(event);
                    });
            }

            days.append(cell);
        }
    }

    const openModal = () => {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        render();
        closeModalButton.focus();
    };

    const closeModal = () => {
        if (detail.classList.contains('active')) closeDetail();
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        openModalButton.focus();
    };

    openModalButton.addEventListener('click', openModal);
    closeModalButton.addEventListener('click', closeModal);
    closeDetailButton.addEventListener('click', closeDetail);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    detail.addEventListener('click', e => { if (e.target === detail) closeDetail(); });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (detail.classList.contains('active')) closeDetail();
        else if (modal.classList.contains('active')) closeModal();
        else if (modalTerminer.classList.contains('active')) fermerTerminer();
    });

    document.getElementById('calendar-prev').addEventListener('click', () => { shown = new Date(shown.getFullYear(), shown.getMonth() - 1, 1); render(); });
    document.getElementById('calendar-next').addEventListener('click', () => { shown = new Date(shown.getFullYear(), shown.getMonth() + 1, 1); render(); });
    document.getElementById('calendar-today').addEventListener('click', () => { shown = new Date(today.getFullYear(), today.getMonth(), 1); render(); });

    render();
})();
</script>

@endsection
