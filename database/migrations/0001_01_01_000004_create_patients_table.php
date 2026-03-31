<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date_naissance')->nullable();
            $table->string('cin', 10)->unique()->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville', 100)->nullable();
            $table->enum('groupe_sanguin', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->text('antecedents')->nullable();
            $table->text('antecedents_familiaux')->nullable();
            $table->text('allergies')->nullable();
            $table->decimal('poids_kg', 5, 2)->nullable();
            $table->integer('taille_cm')->nullable();
            $table->text('traitement_en_cours')->nullable();
            $table->date('date_creation_dossier')->nullable();
            $table->timestamp('dossier_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
