<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ville', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('slug', 255)->nullable();
            $table->string('nom', 45);
            $table->string('nom_reel', 45)->nullable();
            $table->string('code_postal', 255)->nullable();
            $table->string('commune', 3)->nullable();
            $table->string('code_commune', 5)->nullable();
            $table->unsignedSmallInteger('arrondissement')->nullable();
            $table->string('canton', 4)->nullable();
            $table->integer('id_departement')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ville');
    }
};
