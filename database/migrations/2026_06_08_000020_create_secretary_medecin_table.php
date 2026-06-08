<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretary_medecin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secretary_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');
            $table->enum('statut', ['en_attente', 'approuvee', 'refusee'])->default('en_attente');
            $table->timestamp('date_demande')->useCurrent();
            $table->timestamp('date_decision')->nullable();
            $table->text('motif_refus')->nullable();
            $table->timestamps();

            $table->unique('secretary_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretary_medecin');
    }
};
