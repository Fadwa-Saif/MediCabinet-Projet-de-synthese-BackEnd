<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AnalyseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\DisponibiliteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrdonnanceController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\RendezVousController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login',    [AuthController::class, 'login']);

// ── Protected — all use role middleware which handles JWT ─────────────
Route::get('auth/me',      [AuthController::class, 'me'])->middleware('role:medecin,secretaire,patient');
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('role:medecin,secretaire,patient');
Route::post('auth/refresh',[AuthController::class, 'refresh'])->middleware('role:medecin,secretaire,patient');

// Notifications
Route::middleware('role:medecin,secretaire,patient')->prefix('notifications')->group(function () {
    Route::get('/',                     [NotificationController::class, 'index']);
    Route::get('non-lues',              [NotificationController::class, 'nonLues']);
    Route::post('tout-lire',            [NotificationController::class, 'toutMarquerLu']);
    Route::patch('{notification}/lire', [NotificationController::class, 'marquerLu']);
});

// Shared
Route::get('rendezvous/creneaux', [RendezVousController::class, 'creneaux'])->middleware('role:medecin,secretaire,patient');
Route::get('disponibilites',      [DisponibiliteController::class, 'index'])->middleware('role:medecin,secretaire,patient');

// ── Patient ───────────────────────────────────────────────────────────
Route::middleware('role:patient')->group(function () {
    Route::get('rendezvous',                              [RendezVousController::class, 'index']);
    Route::post('rendezvous',                             [RendezVousController::class, 'store']);
    Route::get('rendezvous/{rendezvous}',                 [RendezVousController::class, 'show']);
    Route::patch('rendezvous/{rendezvous}/annuler',       [RendezVousController::class, 'annuler']);
    Route::post('analyses',                               [AnalyseController::class, 'store']);
    Route::get('consultations/{consultationId}/analyses', [AnalyseController::class, 'index']);
    Route::get('patients/{patient}',                      [PatientController::class, 'show']);
    Route::patch('patients/{patient}/profil',             [PatientController::class, 'updateProfil']);
});

// ── Médecin ───────────────────────────────────────────────────────────
Route::middleware('role:medecin')->group(function () {
    Route::get('patients',                                [PatientController::class, 'index']);
    Route::get('patients/{patient}',                      [PatientController::class, 'show']);
    Route::patch('patients/{patient}',                    [PatientController::class, 'update']);
    Route::get('patients/{patientId}/historique',         [ConsultationController::class, 'historique']);

    Route::get('rendezvous',                              [RendezVousController::class, 'index']);
    Route::get('rendezvous/{rendezvous}',                 [RendezVousController::class, 'show']);
    Route::patch('rendezvous/{rendezvous}',               [RendezVousController::class, 'update']);

    Route::post('consultations',                          [ConsultationController::class, 'store']);
    Route::get('consultations/{consultation}',            [ConsultationController::class, 'show']);
    Route::patch('consultations/{consultation}',          [ConsultationController::class, 'update']);

    Route::post('ordonnances',                            [OrdonnanceController::class, 'store']);
    Route::get('ordonnances/{ordonnance}',                [OrdonnanceController::class, 'show']);

    Route::get('consultations/{consultationId}/analyses', [AnalyseController::class, 'index']);
    Route::patch('analyses/{analyse}/annoter',            [AnalyseController::class, 'annoter']);
    Route::delete('analyses/{analyse}',                   [AnalyseController::class, 'destroy']);

    Route::post('disponibilites/sync',                    [DisponibiliteController::class, 'sync']);
    Route::post('disponibilites/bloquer',                 [DisponibiliteController::class, 'bloquer']);
});

// ── Secrétaire ────────────────────────────────────────────────────────
Route::middleware('role:secretaire')->group(function () {
    Route::get('patients',                                [PatientController::class, 'index']);
    Route::get('patients/{patient}',                      [PatientController::class, 'show']);
    Route::get('rendezvous',                              [RendezVousController::class, 'index']);
    Route::get('rendezvous/{rendezvous}',                 [RendezVousController::class, 'show']);
    Route::patch('rendezvous/{rendezvous}',               [RendezVousController::class, 'update']);
    Route::patch('rendezvous/{rendezvous}/annuler',       [RendezVousController::class, 'annuler']);
});

// ── Admin ─────────────────────────────────────────────────────────────
Route::middleware('role:medecin')->prefix('admin')->group(function () {
    Route::get('dashboard',                               [AdminController::class, 'dashboard']);
    Route::get('admins',                                  [AdminController::class, 'index']);
    Route::post('admins',                                 [AdminController::class, 'store']);
    Route::patch('users/{user}/toggle-active',            [AdminController::class, 'toggleActive']);
    Route::delete('patients/{patient}',                   [PatientController::class, 'destroy']);
});