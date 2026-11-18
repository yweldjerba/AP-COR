<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - GSB</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gsb-ajouts.css') }}">
</head>
<body class="login-page">

<div class="login-screen">

    <div class="login-card inscription-card">

        <div class="login-header">
            <div class="login-logo-wrap">
                <img src="{{ asset('images/logo-gsb.png') }}" alt="Galaxy Swiss Bourdin" class="login-logo">
            </div>
            <h1 class="login-title">Créer un compte</h1>
            <p class="login-subtitle">
                Espace patient {{ $region->nom_reel ?? '' }}
            </p>
        </div>

        @if($errors->any())
            <div class="login-error">
                @foreach($errors->all() as $erreur)
                    <div>{{ $erreur }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('inscription.submit') }}">
            @csrf

            <div class="form-row">
                <div class="login-field">
                    <label class="login-label" for="nom">Nom</label>
                    <input class="login-input" id="nom" type="text" name="nom" value="{{ old('nom') }}" autocomplete="family-name" required>
                </div>
                <div class="login-field">
                    <label class="login-label" for="prenom">Prénom</label>
                    <input class="login-input" id="prenom" type="text" name="prenom" value="{{ old('prenom') }}" autocomplete="given-name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="login-field">
                    <label class="login-label" for="email">Adresse email</label>
                    <input class="login-input" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>
                <div class="login-field">
                    <label class="login-label" for="telephone">Téléphone</label>
                    <input class="login-input" id="telephone" type="tel" name="telephone" value="{{ old('telephone') }}" placeholder="0612345678" pattern="0[1-9][0-9]{8}" autocomplete="tel" required>
                </div>
            </div>

            <div class="login-field">
                <label class="login-label" for="adresse">Adresse postale</label>
                <input class="login-input" id="adresse" type="text" name="adresse" value="{{ old('adresse') }}" placeholder="12 rue des Oliviers" autocomplete="street-address" required>
            </div>

            <div class="form-row">
                <div class="login-field">
                    <label class="login-label" for="id_departement">Département</label>
                    <select class="login-select" id="id_departement" name="id_departement" required>
                        <option value="">Choisir…</option>
                        @foreach($departements as $departement)
                            <option value="{{ $departement->id }}" @selected(old('id_departement') == $departement->id)>
                                {{ $departement->code }} - {{ $departement->nom_reel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="login-field">
                    <label class="login-label" for="id_ville">Ville</label>
                    <select class="login-select" id="id_ville" name="id_ville" required disabled>
                        <option value="">Choisissez d'abord un département</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="login-field">
                    <label class="login-label" for="mdp">Mot de passe</label>
                    <input class="login-input" id="mdp" type="password" name="mdp" autocomplete="new-password" required>
                </div>
                <div class="login-field">
                    <label class="login-label" for="mdp_confirmation">Confirmation</label>
                    <input class="login-input" id="mdp_confirmation" type="password" name="mdp_confirmation" autocomplete="new-password" required>
                </div>
            </div>

            <button class="login-submit" type="submit">Créer mon compte</button>
        </form>

        <div class="login-register">
            <span>Vous avez déjà un compte ?</span>
            <a href="{{ route('login') }}">Se connecter</a>
        </div>

    </div>

</div>

<script>
(() => {
    const villes = {{ Illuminate\Support\Js::from($villes) }};
    const selectDepartement = document.getElementById('id_departement');
    const selectVille = document.getElementById('id_ville');
    const ancienneVille = @json(old('id_ville'));

    function remplirVilles() {
        const idDepartement = selectDepartement.value;
        selectVille.replaceChildren();

        if (!idDepartement) {
            selectVille.disabled = true;
            selectVille.add(new Option("Choisissez d'abord un département", ''));
            return;
        }

        const liste = villes.filter(v => String(v.id_departement) === String(idDepartement));

        selectVille.disabled = false;
        selectVille.add(new Option(liste.length ? 'Choisir une ville…' : 'Aucune ville trouvée', ''));

        liste.forEach(ville => {
            const option = new Option(ville.nom_reel, ville.id);
            if (String(ville.id) === String(ancienneVille)) option.selected = true;
            selectVille.add(option);
        });
    }

    selectDepartement.addEventListener('change', remplirVilles);
    remplirVilles();
})();
</script>

</body>
</html>
