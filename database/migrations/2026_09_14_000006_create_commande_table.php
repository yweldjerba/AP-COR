<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commande', function (Blueprint $table) {
            $table->increments('id_commande');
            $table->unsignedInteger('id_praticien');
            $table->dateTime('date_commande');
            $table->dateTime('date_expiration')->nullable();
            $table->string('statut', 20)->default('en_cours');

            $table->foreign('id_praticien')
                ->references('id')
                ->on('praticien')
                ->cascadeOnDelete();

            $table->index(['id_praticien', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande');
    }
};
