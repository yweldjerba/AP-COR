<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicament', function (Blueprint $table) {
            $table->increments('id');
            $table->string('designation', 200);
            $table->string('marque', 100)->nullable();
            $table->string('concentration', 100)->nullable();
            $table->string('type', 200)->nullable();
            $table->string('commentaire', 255)->nullable();
            $table->string('reference', 10)->nullable();
            $table->enum('lp', ['OUI', 'NON'])->default('NON');
            $table->string('dilution', 70)->nullable();
            $table->enum('sans_sulfite', ['OUI', 'NON'])->default('NON');
            $table->enum('sans_sucre', ['OUI', 'NON'])->default('NON');
            $table->enum('nourrissons', ['OUI', 'NON'])->default('NON');
            $table->enum('enfants', ['OUI', 'NON'])->default('NON');
            $table->enum('adultes', ['OUI', 'NON'])->default('OUI');
            $table->string('arome_1', 10)->nullable();
            $table->string('arome_2', 10)->nullable();
            $table->enum('importation', ['OUI', 'NON'])->default('NON');
            $table->double('Prix')->default(0);
            $table->integer('stock')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicament');
    }
};
