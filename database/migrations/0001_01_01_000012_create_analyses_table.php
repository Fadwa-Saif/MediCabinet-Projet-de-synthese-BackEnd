<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('patient_id')
                  ->nullable()
                  ->constrained('patients')
                  ->nullOnDelete();

            $table->foreignId('consultation_id')
                  ->nullable()
                  ->constrained('consultations')
                  ->nullOnDelete();

            $table->foreignId('centre_id')
                  ->nullable()
                  ->constrained('centres_radio_analyse')
                  ->nullOnDelete();

            // Analysis info (sent by frontend)
            $table->string('type_analyse')->nullable();
            $table->string('laboratoire')->nullable();
            $table->date('date_analyse')->nullable();
            $table->date('date_resultat')->nullable();
            $table->string('fichier')->nullable();

            // Comments
            $table->text('commentaire_patient')->nullable();
            $table->text('commentaire_medecin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};