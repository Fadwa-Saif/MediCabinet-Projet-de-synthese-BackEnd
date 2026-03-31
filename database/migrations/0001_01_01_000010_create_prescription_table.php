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
        Schema::create('prescription', function (Blueprint $table) {
            $table->foreignId('ordonnance_id')->constrained('ordonnances')->onDelete('cascade');
            $table->foreignId('medicament_id')->constrained('medicaments')->onDelete('cascade');
            $table->text('posologie');
            $table->text('observation')->nullable();
            $table->integer('quantite')->default(1);
            $table->string('duree_traitement', 50)->nullable();
            $table->primary(['ordonnance_id', 'medicament_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription');
    }
};
