<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PraticienController;
use App\Http\Middleware\VerifierRole;
use Illuminate\Support\Facades\Route;

/* ---------- Authentification ---------- */

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/inscription', [AuthController::class, 'showInscription'])->name('inscription');
Route::post('/inscription', [AuthController::class, 'inscription'])->name('inscription.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/* ---------- Espace client ---------- */

Route::prefix('client')
    ->name('client.')
    ->middleware(VerifierRole::class . ':client')
    ->group(function () {
        Route::get('/', [ClientController::class, 'accueil'])->name('accueil');
        Route::get('/creneaux', [ClientController::class, 'creneaux'])->name('creneaux');
        Route::post('/rendez-vous', [ClientController::class, 'store'])->name('rendez-vous.store');
        Route::get('/dossier-medical', [ClientController::class, 'dossier'])->name('dossier');
    });


/* ---------- Espace praticien ---------- */

Route::prefix('praticien')
    ->name('praticien.')
    ->middleware(VerifierRole::class . ':praticien')
    ->group(function () {
        Route::get('/', [PraticienController::class, 'accueil'])->name('accueil');

        Route::post('/rendez-vous/{id}/accepter', [PraticienController::class, 'accepter'])->name('rendez-vous.accepter');
        Route::post('/rendez-vous/{id}/refuser', [PraticienController::class, 'refuser'])->name('rendez-vous.refuser');
        Route::post('/rendez-vous/{id}/terminer', [PraticienController::class, 'terminer'])->name('rendez-vous.terminer');

        Route::get('/patients', [PraticienController::class, 'patients'])->name('patients');

        Route::get('/horaires', [PraticienController::class, 'horaires'])->name('horaires');
        Route::post('/horaires', [PraticienController::class, 'updateHoraires'])->name('horaires.update');
    });
