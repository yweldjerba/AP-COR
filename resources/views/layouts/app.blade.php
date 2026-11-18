<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GSB')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gsb-ajouts.css') }}">
</head>
<body>

<div class="app-shell">

    <header class="topbar">

        <div class="topbar-left">
            @php
                $accueil = match (session('user_role')) {
                    'client'    => route('client.accueil'),
                    'praticien' => route('praticien.accueil'),
                    default     => route('login'),
                };
            @endphp

            <a href="{{ $accueil }}" class="brand-link">
                <img src="{{ asset('images/logo-gsb.png') }}" alt="GSB" class="brand-logo">
                <div class="brand-title">
                    <strong>GSB</strong>
                    <span>Gestion des rendez-vous</span>
                </div>
            </a>
        </div>

        <nav class="topbar-nav">
            @if(session('user_role') === 'client')
                <a href="{{ route('client.accueil') }}" class="{{ request()->routeIs('client.accueil') ? 'active' : '' }}">
                    Mes rendez-vous
                </a>
                <a href="{{ route('client.dossier') }}" class="{{ request()->routeIs('client.dossier') ? 'active' : '' }}">
                    Mon dossier médical
                </a>
            @elseif(session('user_role') === 'praticien')
                <a href="{{ route('praticien.accueil') }}" class="{{ request()->routeIs('praticien.accueil') ? 'active' : '' }}">
                    Rendez-vous
                </a>
                <a href="{{ route('praticien.patients') }}" class="{{ request()->routeIs('praticien.patients') ? 'active' : '' }}">
                    Mes patients
                </a>
                <a href="{{ route('praticien.horaires') }}" class="{{ request()->routeIs('praticien.horaires') ? 'active' : '' }}">
                    Mes horaires
                </a>
            @endif
        </nav>

        @if(session('user_role'))
            <div class="topbar-right">
                <div class="user-chip">
                    <div class="user-avatar">
                        {{ strtoupper(substr(session('user_prenom') ?? session('user_nom') ?? 'U', 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <strong>{{ session('user_prenom') }} {{ session('user_nom') }}</strong>
                        <span>{{ session('user_role') === 'client' ? 'Client' : 'Praticien' }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline">Déconnexion</button>
                </form>
            </div>
        @endif

    </header>

    <main class="main-container">

        @if(session('notification'))
            <div class="alert alert-info" role="status">
                🔔 {{ session('notification') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')

    </main>

</div>

</body>
</html>
