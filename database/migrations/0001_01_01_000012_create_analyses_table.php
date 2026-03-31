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
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->onDelete('set null');
            $table->foreignId('centre_id')->nullable()->constrained('centres_radio_analyse')->onDelete('set null');
            $table->enum('type_analyse', ['biologie', 'autre'])->nullable();
            $table->date('date_analyse')->nullable();
            $table->date('date_resultat')->nullable();
            $table->string('fichier', 255)->nullable();
            $table->text('commentaire_medecin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
