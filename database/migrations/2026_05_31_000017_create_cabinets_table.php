<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->text('adresse');
            $table->string('ville', 100)->nullable();
            $table->string('specialite', 120);
            $table->foreignId('docteur_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('docteur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabinets');
    }
};