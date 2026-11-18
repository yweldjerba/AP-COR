<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_commande', function (Blueprint $table) {
            $table->increments('id_ligne');
            $table->unsignedInteger('id_commande');
            $table->unsignedInteger('id_medicament');
            $table->integer('quantite');

            $table->foreign('id_commande')
                ->references('id_commande')
                ->on('commande')
                ->cascadeOnDelete();

            $table->foreign('id_medicament')
                ->references('id')
                ->on('medicament')
                ->restrictOnDelete();

            $table->unique(['id_commande', 'id_medicament']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_commande');
    }
};
