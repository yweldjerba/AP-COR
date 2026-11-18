@extends('layouts.app')

@section('title', 'Mes rendez-vous - GSB')

@section('content')

@php
    $nbAVenir = $aVenir->count();
    $enAttente = $rendezVous->where('statut', 'en_attente')->count();
    $termines = $rendezVous->where('statut', 'termine')->count();
    $refuses = $rendezVous->where('statut', 'refuse')->count();

    $praticiensJson = $praticiens->map(fn ($p) => [
        'id' => $p->id,
        'nom' => $p->nom ?? '',
        'prenom' => $p->prenom ?? '',
        'ville' => $p->ville ?? '',
        'departement' => $p->departement ?? '',
        'code_departement' => $p->code_departement ?? '',
        'meme_ville' => (bool) $p->meme_ville,
        'meme_departement' => (bool) $p->meme_departement,
    ])->values();

    $aUnDepartement = !empty($client->id_departement);
@endphp

<div class="client-page">

    <div class="client-header">
        <div>
            <span class="eyebrow">Espace client</span>
            <h1>Bonjour {{ session('user_prenom') ?? session('user_nom') ?? 'Client' }}</h1>
            <p>Prenez rendez-vous, suivez vos demandes et retrouvez votre historique.</p>
        </div>

        <button type="button" class="btn btn-primary" onclick="ouvrirModalRdv()">
            + Prendre un rendez-vous
        </button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">À venir</span>
            <strong>{{ $nbAVenir }}</strong>
            <span class="stat-text">Rendez-vous prévus</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">En attente</span>
            <strong>{{ $enAttente }}</strong>
            <span class="stat-text">À confirmer par le praticien</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Terminés</span>
            <strong>{{ $termines }}</strong>
            <span class="stat-text">Consultations passées</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Refusés</span>
            <strong>{{ $refuses }}</strong>
            <span class="stat-text">Non retenus</span>
        </div>
    </div>

    {{-- ---------- RDV À VENIR ---------- --}}

    <div class="section-header">
        <div>
            <span class="eyebrow">Planning</span>
            <h2>Rendez-vous à venir</h2>
            <p>Vos demandes en attente et vos rendez-vous confirmés.</p>
        </div>
        @if($enAttente > 0)
            <span class="pending-count">
                {{ $enAttente }} {{ $enAttente > 1 ? 'demandes en attente' : 'demande en attente' }}
            </span>
        @endif
    </div>

    <div class="rdv-card rdv-section">
        <div class="rdv-card-header">
            <div>
                <h3>Prochains rendez-vous</h3>
                <p>Classés du plus proche au plus lointain.</p>
            </div>
            <span class="total-count">{{ $nbAVenir }} rendez-vous</span>
        </div>
        <div class="rdv-card-body">
            @if($aVenir->isEmpty() && $historique->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <h3>Aucun rendez-vous</h3>
                    <p>Vous n'avez pas encore fait de demande de rendez-vous.</p>
                    <button type="button" class="btn btn-primary" onclick="ouvrirModalRdv()">Prendre un rendez-vous</button>
                </div>
            @else
                @include('client.partials.table-rdv', [
                    'liste' => $aVenir,
                    'vide' => 'Aucun rendez-vous à venir.',
                ])
            @endif
        </div>
    </div>

    {{-- ---------- HISTORIQUE ---------- --}}

    <div class="section-header">
        <div>
            <span class="eyebrow">Historique</span>
            <h2>Anciens rendez-vous</h2>
            <p>Rendez-vous passés, terminés ou refusés.</p>
        </div>
    </div>

    <div class="rdv-card rdv-section">
        <div class="rdv-card-header">
            <div>
                <h3>Historique</h3>
                <p>Du plus récent au plus ancien.</p>
            </div>
            <span class="total-count">{{ $historique->count() }} rendez-vous</span>
        </div>
        <div class="rdv-card-body">
            @include('client.partials.table-rdv', [
                'liste' => $historique,
                'vide' => 'Aucun rendez-vous dans votre historique.',
            ])
        </div>
    </div>

</div>

<style>
    .rdv-detail-dialog { width: min(90vw, 460px); max-height: 85vh; border: 0; border-radius: 14px; padding: 24px; box-shadow: 0 16px 50px #0003; }
    .rdv-detail-dialog::backdrop { background: #0008; }
    .rdv-detail-dialog dl { display: grid; grid-template-columns: 100px 1fr; gap: 12px; margin: 20px 0; }
    .rdv-detail-dialog dt { font-weight: 700; }
    .rdv-detail-motif { white-space: pre-wrap; overflow-wrap: anywhere; }
</style>


{{-- =========================================================
     MODALE : PRISE DE RENDEZ-VOUS
     ========================================================= --}}

<div id="modalRdv" class="modal-overlay {{ $errors->any() ? 'active' : '' }}">
    <div class="modal-box">

        <div class="modal-header">
            <div>
                <span class="eyebrow">Nouvelle demande</span>
                <h2>Prendre un rendez-vous</h2>
                <p>Choisissez un praticien, une date puis un créneau.</p>
            </div>
            <button type="button" class="modal-close" onclick="fermerModalRdv()" aria-label="Fermer">×</button>
        </div>

        @if($errors->any())
            <div class="form-errors">
                @foreach($errors->all() as $erreur)
                    <p>{{ $erreur }}</p>
                @endforeach
            </div>
        @endif

        <form id="formRdv" method="POST" action="{{ route('client.rendez-vous.store') }}">
            @csrf

            <div class="modal-body">

                {{-- 1. Praticien --}}

                <div class="praticien-filter-buttons">
                    <button type="button" id="btnTous" class="praticien-filter active" onclick="changerMode('tous')">
                        Tous les praticiens
                    </button>
                    <button type="button" id="btnDepartement" class="praticien-filter" onclick="changerMode('departement')">
                        Praticiens proches
                    </button>
                </div>

                <div id="zonePraticien" class="form-group praticien-search">
                    <label for="recherche_praticien">Praticien</label>
                    <input type="text" id="recherche_praticien" class="form-control" placeholder="Cliquez ou tapez les premières lettres du nom..." autocomplete="off">
                    <div id="suggestionsPraticiens" class="praticien-suggestions"></div>
                    <small id="infoMode" class="proximite-info"></small>
                </div>

                <input type="hidden" id="id_praticien" name="id_praticien" value="{{ old('id_praticien') }}">

                <div id="praticienChoisi" class="praticien-selection"></div>

                {{-- 2. Date --}}

                <div class="form-group">
                    <label for="date_rdv">Date</label>
                    <input type="date" id="date_rdv" name="date_rdv" class="form-control" value="{{ old('date_rdv') }}" min="{{ date('Y-m-d') }}" required>
                </div>

                {{-- 3. Créneau --}}

                <div class="form-group">
                    <label>Créneau</label>
                    <input type="hidden" id="heure_rdv" name="heure_rdv" value="{{ old('heure_rdv') }}">
                    <div id="creneaux" class="creneaux-grid" aria-live="polite">
                        <p class="creneaux-message">Choisissez d'abord un praticien et une date.</p>
                    </div>
                </div>

                {{-- 4. Motif --}}

                <div class="form-group">
                    <label for="motif">Motif</label>
                    <textarea id="motif" name="motif" class="form-control" rows="3" maxlength="255" placeholder="Indiquez le motif du rendez-vous..." required>{{ old('motif') }}</textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fermerModalRdv()">Annuler</button>
                <button type="submit" class="btn btn-primary">Envoyer la demande</button>
            </div>
        </form>

    </div>
</div>


<script>
const praticiens = {{ Illuminate\Support\Js::from($praticiensJson) }};
const aUnDepartement = @json($aUnDepartement);
const urlCreneaux = @json(route('client.creneaux'));

const modalRdv = document.getElementById('modalRdv');
const formRdv = document.getElementById('formRdv');
const champRecherche = document.getElementById('recherche_praticien');
const suggestions = document.getElementById('suggestionsPraticiens');
const praticienChoisi = document.getElementById('praticienChoisi');
const idPraticien = document.getElementById('id_praticien');
const infoMode = document.getElementById('infoMode');
const btnDepartement = document.getElementById('btnDepartement');
const btnTous = document.getElementById('btnTous');
const champDate = document.getElementById('date_rdv');
const champHeure = document.getElementById('heure_rdv');
const zoneCreneaux = document.getElementById('creneaux');

let mode = 'tous';
let joursPraticien = [];


/* ---------- Modale ---------- */

function ouvrirModalRdv() {
    modalRdv.classList.add('active');
    document.body.classList.add('modal-open');
}

function fermerModalRdv() {
    modalRdv.classList.remove('active');
    document.body.classList.remove('modal-open');
    fermerListe();
}


/* ---------- Mode : mon département / tous ---------- */

function changerMode(nouveauMode) {
    mode = nouveauMode;
    btnDepartement.classList.toggle('active', mode === 'departement');
    btnTous.classList.toggle('active', mode === 'tous');

    infoMode.textContent = mode === 'departement'
        ? (aUnDepartement
            ? 'Praticiens de votre département uniquement. Ceux de votre ville apparaissent en premier.'
            : 'Votre département n\'est pas renseigné : utilisez « Tous les praticiens ».')
        : 'Tous les praticiens de Corse : Corse-du-Sud (2A) et Haute-Corse (2B).';

    champRecherche.value = '';
    fermerListe();
}


/* ---------- Texte ---------- */

function normaliser(texte) {
    return String(texte ?? '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function localisation(p) {
    const morceaux = [];
    if (p.ville) morceaux.push(p.ville);
    if (p.departement) morceaux.push(p.departement + (p.code_departement ? ' (' + String(p.code_departement).toUpperCase() + ')' : ''));
    return morceaux.join(', ') || 'Localisation non renseignée';
}

function ajouterBadge(conteneur, p) {
    if (!p.meme_ville) return;
    const badge = document.createElement('small');
    badge.className = 'badge-proche';
    badge.textContent = 'Dans votre ville';
    conteneur.appendChild(badge);
}


/* ---------- Liste des praticiens ---------- */

function filtrer(texte) {
    const recherche = normaliser(texte.trim());

    return praticiens
        // « Tous les praticiens » : 2A + 2B
        // « Praticiens proches » : uniquement ceux du département du client
        .filter(p => mode === 'tous' || p.meme_departement)
        .filter(p => {
            if (!recherche) return true;
            const nom = normaliser(p.nom);
            const prenom = normaliser(p.prenom);
            return nom.startsWith(recherche)
                || prenom.startsWith(recherche)
                || (prenom + ' ' + nom).startsWith(recherche)
                || (nom + ' ' + prenom).startsWith(recherche);
        })
        .sort((a, b) =>
            (Number(b.meme_ville) - Number(a.meme_ville))
            || (Number(b.meme_departement) - Number(a.meme_departement))
            || a.nom.localeCompare(b.nom, 'fr', { sensitivity: 'base' })
            || a.prenom.localeCompare(b.prenom, 'fr', { sensitivity: 'base' })
        )
        .slice(0, 100);
}

function afficherListe() {
    suggestions.innerHTML = '';
    const resultats = filtrer(champRecherche.value);

    if (resultats.length === 0) {
        const vide = document.createElement('div');
        vide.className = 'praticien-suggestion-empty';
        vide.textContent = mode === 'departement'
            ? 'Aucun praticien proche trouvé. Essayez « Tous les praticiens ».'
            : 'Aucun praticien trouvé.';
        suggestions.appendChild(vide);
        return;
    }

    resultats.forEach(p => {
        const bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.className = 'praticien-suggestion';

        const nom = document.createElement('strong');
        nom.textContent = p.prenom + ' ' + p.nom;

        const lieu = document.createElement('span');
        lieu.textContent = localisation(p);

        bouton.append(nom, lieu);
        ajouterBadge(bouton, p);

        bouton.addEventListener('mousedown', e => e.preventDefault());
        bouton.addEventListener('click', () => choisirPraticien(p));

        suggestions.appendChild(bouton);
    });
}

function fermerListe() {
    suggestions.innerHTML = '';
}

function choisirPraticien(p, heureAPreselectionner = '') {
    idPraticien.value = p.id;
    champRecherche.value = p.prenom + ' ' + p.nom;
    fermerListe();

    praticienChoisi.innerHTML = '';
    const nom = document.createElement('strong');
    nom.textContent = p.prenom + ' ' + p.nom;
    const lieu = document.createElement('span');
    lieu.textContent = localisation(p);
    const jours = document.createElement('span');
    jours.id = 'joursPraticien';
    jours.className = 'jours-praticien';
    praticienChoisi.append(nom, lieu, jours);
    ajouterBadge(praticienChoisi, p);

    chargerCreneaux(heureAPreselectionner);
}


/* ---------- Créneaux ---------- */

function messageCreneaux(texte) {
    zoneCreneaux.innerHTML = '';
    const p = document.createElement('p');
    p.className = 'creneaux-message';
    p.textContent = texte;
    zoneCreneaux.appendChild(p);
}

async function chargerCreneaux(heureAPreselectionner = '') {
    champHeure.value = '';

    if (!idPraticien.value) {
        messageCreneaux('Choisissez d\'abord un praticien et une date.');
        return;
    }

    const params = new URLSearchParams({ id_praticien: idPraticien.value });
    if (champDate.value) params.append('date', champDate.value);

    messageCreneaux('Chargement des créneaux…');

    try {
        const reponse = await fetch(urlCreneaux + '?' + params, { headers: { Accept: 'application/json' } });
        const donnees = await reponse.json();

        const zoneJours = document.getElementById('joursPraticien');
        if (zoneJours && donnees.jours) {
            zoneJours.textContent = donnees.jours.length
                ? 'Consulte : ' + donnees.jours.join(', ')
                : 'Aucun jour de consultation défini';
        }

        if (!champDate.value) {
            messageCreneaux('Choisissez maintenant une date.');
            return;
        }

        if (!reponse.ok || donnees.creneaux.length === 0) {
            messageCreneaux(donnees.message || 'Aucun créneau disponible.');
            return;
        }

        zoneCreneaux.innerHTML = '';

        donnees.creneaux.forEach(heure => {
            const bouton = document.createElement('button');
            bouton.type = 'button';
            bouton.className = 'creneau';
            bouton.textContent = heure;
            bouton.setAttribute('aria-pressed', 'false');

            bouton.addEventListener('click', () => choisirCreneau(bouton, heure));

            zoneCreneaux.appendChild(bouton);

            if (heure === heureAPreselectionner) choisirCreneau(bouton, heure);
        });
    } catch (erreur) {
        messageCreneaux('Impossible de charger les créneaux. Réessayez.');
    }
}

function choisirCreneau(bouton, heure) {
    zoneCreneaux.querySelectorAll('.creneau').forEach(b => {
        b.classList.remove('selected');
        b.setAttribute('aria-pressed', 'false');
    });
    bouton.classList.add('selected');
    bouton.setAttribute('aria-pressed', 'true');
    champHeure.value = heure;
}


/* ---------- Événements ---------- */

champRecherche.addEventListener('click', afficherListe);

champRecherche.addEventListener('input', () => {
    idPraticien.value = '';
    praticienChoisi.innerHTML = '';
    messageCreneaux('Choisissez d\'abord un praticien et une date.');
    afficherListe();
});

champRecherche.addEventListener('keydown', e => {
    if (e.key === 'ArrowDown') afficherListe();
});

document.getElementById('zonePraticien').addEventListener('focusout', e => {
    if (!e.currentTarget.contains(e.relatedTarget)) fermerListe();
});

document.addEventListener('click', e => {
    if (e.target !== champRecherche && !e.target.closest('.praticien-suggestions')) fermerListe();
});

champDate.addEventListener('change', () => chargerCreneaux());

formRdv.addEventListener('submit', e => {
    let erreur = '';

    if (!idPraticien.value) erreur = 'Veuillez sélectionner un praticien.';
    else if (!champHeure.value) erreur = 'Veuillez choisir un créneau horaire.';

    if (erreur) {
        e.preventDefault();
        const span = document.createElement('span');
        span.className = 'selection-error';
        span.textContent = erreur;
        praticienChoisi.querySelector('.selection-error')?.remove();
        praticienChoisi.appendChild(span);
    }
});

modalRdv.addEventListener('click', e => {
    if (e.target === modalRdv) fermerModalRdv();
});

document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (suggestions.children.length) fermerListe();
    else fermerModalRdv();
});


/* ---------- Initialisation ---------- */

changerMode(mode);

@if($errors->any())
    document.body.classList.add('modal-open');

    (() => {
        const ancien = praticiens.find(p => String(p.id) === String(idPraticien.value));
        if (ancien) {
            const heure = @json(old('heure_rdv'));
            choisirPraticien(ancien, heure || '');
        }
    })();
@endif
</script>

@endsection