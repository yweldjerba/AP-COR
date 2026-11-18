<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion - GSB</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="login-page">

<div class="login-screen">

    <div class="login-card">

        <div class="login-header">

            <div class="login-logo-wrap">
                <img
                    src="{{ asset('images/logo-gsb.png') }}"
                    alt="Galaxy Swiss Bourdin"
                    class="login-logo"
                >
            </div>

            <h1 class="login-title">
                Connexion
            </h1>

            <p class="login-subtitle">
                Espace rendez-vous GSB
            </p>

        </div>

        @if(session('success'))
            <div class="login-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="login-error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="login-error">
                @foreach($errors->all() as $erreur)
                    <div>{{ $erreur }}</div>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('login.submit') }}"
        >
            @csrf

            <div class="login-field">

                <label class="login-label" for="login">
                    Identifiant
                </label>

                <input
                    class="login-input"
                    id="login"
                    type="text"
                    name="login"
                    value="{{ old('login') }}"
                    placeholder="Votre identifiant"
                    autocomplete="username"
                    autocapitalize="none"
                    spellcheck="false"
                    required
                    autofocus
                >

            </div>

            <div class="login-field login-field-password">

                <label class="login-label" for="mdp">
                    Mot de passe
                </label>

                <input
                    class="login-input"
                    id="mdp"
                    type="password"
                    name="mdp"
                    placeholder="Votre mot de passe"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button class="login-submit" type="submit">
                Se connecter
            </button>

        </form>

        <div class="login-register">
            <span>Vous n'avez pas encore de compte ?</span>

            <a href="{{ route('inscription') }}">
                Créer un compte
            </a>
        </div>

    </div>

</div>

</body>
</html>