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
        Schema::create('disponibilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->enum('jour_semaine', ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']);
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->integer('duree_min')->default(30);
            $table->tinyInteger('est_disponible')->default(1);
            $table->date('date_exception')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilites');
    }
};
