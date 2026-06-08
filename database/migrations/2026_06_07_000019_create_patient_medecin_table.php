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
        Schema::create('patient_medecin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('medecin_id')->constrained('admins')->onDelete('cascade');
            $table->timestamp('date_affectation')->useCurrent();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();

            // Unique constraint: one patient can't have multiple active assignments to the same doctor
            $table->unique(['patient_id', 'medecin_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medecin');
    }
};
