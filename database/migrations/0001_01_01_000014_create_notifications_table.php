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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediteur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('destinataire_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['rdv_rappel', 'confirmation', 'nouvelle_analyse', 'message', 'annulation', 'systeme']);
            $table->enum('canal', ['email', 'sms', 'web'])->default('web');
            $table->string('titre', 100);
            $table->text('contenu');
            $table->tinyInteger('lu')->default(0);
            $table->dateTime('date_envoi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
