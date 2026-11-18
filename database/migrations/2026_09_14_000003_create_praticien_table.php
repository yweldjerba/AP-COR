<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('praticien', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nom', 50);
            $table->string('prenom', 60);
            $table->string('adresse', 100)->nullable();
            $table->float('coef_notoriete')->default(0);
            $table->double('salaire')->default(0);
            $table->string('code_type_praticien', 6)->nullable();
            $table->unsignedMediumInteger('id_ville')->nullable();

            $table->foreign('code_type_praticien')
                ->references('code')
                ->on('type_praticien')
                ->nullOnDelete();

            $table->foreign('id_ville')
                ->references('id')
                ->on('ville')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('praticien');
    }
};
