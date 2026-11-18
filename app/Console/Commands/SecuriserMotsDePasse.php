<?php

namespace App\Console\Commands;

use App\Models\Utilisateur;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SecuriserMotsDePasse extends Command
{
    protected $signature = 'gsb:securiser-mots-de-passe';
    protected $description = 'Convertit les mots de passe en clair de la table utilisateur en hashes sécurisés.';

    public function handle(): int
    {
        $modifies = 0;

        Utilisateur::query()->orderBy('id')->chunkById(100, function ($utilisateurs) use (&$modifies) {
            foreach ($utilisateurs as $utilisateur) {
                $mdp = (string) $utilisateur->mdp;

                if (
                    str_starts_with($mdp, '$2y$')
                    || str_starts_with($mdp, '$2a$')
                    || str_starts_with($mdp, '$argon2')
                ) {
                    continue;
                }

                $utilisateur->mdp = Hash::make($mdp);
                $utilisateur->save();
                $modifies++;
            }
        }, 'id');

        $this->info("{$modifies} mot(s) de passe sécurisé(s).");
        return self::SUCCESS;
    }
}
