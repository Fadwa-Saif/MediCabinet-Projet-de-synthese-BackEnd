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
        Schema::create('radiologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->onDelete('set null');
            $table->foreignId('centre_id')->nullable()->constrained('centres_radio_analyse')->onDelete('set null');
            $table->string('type_radio', 100)->nullable();
            $table->date('date_examen')->nullable();
            $table->date('date_resultat')->nullable();
            $table->string('fichier', 255)->nullable();
            $table->text('interpretation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiologies');
    }
};
