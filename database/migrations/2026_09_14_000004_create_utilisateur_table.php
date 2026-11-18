<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nom', 50);
            $table->string('mdp', 255);
            $table->string('login', 50)->unique();
            $table->string('role', 20);
            $table->unsignedInteger('id_prat')->nullable();

            $table->foreign('id_prat')
                ->references('id')
                ->on('praticien')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateur');
    }
};
