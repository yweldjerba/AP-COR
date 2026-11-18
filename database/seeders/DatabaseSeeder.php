<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('type_praticien')->insert([
            'code' => 'GEN',
            'libelle' => 'Médecin généraliste',
            'lieu' => 'Cabinet',
            'type' => 'M',
        ]);

        $villeId = DB::table('ville')->insertGetId([
            'slug' => 'ville-test',
            'nom' => 'Ville Test',
            'nom_reel' => 'Ville Test',
            'code_postal' => '59000',
            'commune' => '001',
            'code_commune' => '59001',
            'arrondissement' => 1,
            'canton' => '001',
            'id_departement' => null,
        ]);

        $praticienId = DB::table('praticien')->insertGetId([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'adresse' => '10 rue de la Santé',
            'coef_notoriete' => 1,
            'salaire' => 2500,
            'code_type_praticien' => 'GEN',
            'id_ville' => $villeId,
        ]);

        DB::table('utilisateur')->insert([
            [
                'nom' => 'Responsable RH',
                'login' => 'rh',
                'mdp' => '1234',
                'role' => 'RH',
                'id_prat' => null,
            ],
            [
                'nom' => 'Responsable Commandes',
                'login' => 'responsable',
                'mdp' => '1234',
                'role' => 'responsable',
                'id_prat' => null,
            ],
            [
                'nom' => 'Jean Dupont',
                'login' => 'praticien',
                'mdp' => '1234',
                'role' => 'praticien',
                'id_prat' => $praticienId,
            ],
        ]);

        DB::table('medicament')->insert([
            [
                'designation' => 'Doliprane 500 mg',
                'marque' => 'Sanofi',
                'concentration' => '500 mg',
                'type' => 'Comprimé',
                'commentaire' => 'Médicament de test',
                'reference' => 'DOL500',
                'lp' => 'NON',
                'dilution' => null,
                'sans_sulfite' => 'OUI',
                'sans_sucre' => 'OUI',
                'nourrissons' => 'NON',
                'enfants' => 'OUI',
                'adultes' => 'OUI',
                'arome_1' => null,
                'arome_2' => null,
                'importation' => 'NON',
                'Prix' => 2.50,
                'stock' => 100,
            ],
            [
                'designation' => 'Spasfon',
                'marque' => 'Teva',
                'concentration' => '80 mg',
                'type' => 'Comprimé',
                'commentaire' => 'Médicament de test',
                'reference' => 'SPA080',
                'lp' => 'NON',
                'dilution' => null,
                'sans_sulfite' => 'OUI',
                'sans_sucre' => 'NON',
                'nourrissons' => 'NON',
                'enfants' => 'OUI',
                'adultes' => 'OUI',
                'arome_1' => null,
                'arome_2' => null,
                'importation' => 'NON',
                'Prix' => 3.90,
                'stock' => 75,
            ],
        ]);
    }
}