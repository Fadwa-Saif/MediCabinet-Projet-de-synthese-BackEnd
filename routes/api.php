<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AnalyseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabinetController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\DisponibiliteController;
use App\Http\Controllers\Api\MedicamentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrdonnanceController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\RendezVousController;
use App\Models\Cabinet;
use App\Models\Patient;
use App\Models\RendezVous;
use Illuminate\Support\Facades\Route;



// ── Public ────────────────────────────────────────────────────────────
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login',    [AuthController::class, 'login']);
Route::get('cabinets',       [CabinetController::class, 'search']);
Route::get('cabinets/search',[CabinetController::class, 'search']);
Route::get('stats', function () {
    return response()->json([
        'cabinets' => Cabinet::count(),
        'patients' => Patient::count(),
        'appointments' => RendezVous::count(),
    ], 200);
});

// ── Protected — all use role middleware which handles JWT ─────────────
Route::middleware('role:medecin,secretaire,patient')->group(function () {
    // Auth & Utilities
    Route::get('auth/me',         [AuthController::class, 'me']);
    Route::post('auth/logout',    [AuthController::class, 'logout']);
    Route::post('auth/refresh',   [AuthController::class, 'refresh']);

    // Profile (all authenticated users)
    Route::get('profil',          [AuthController::class, 'profil']);
    Route::put('profil',          [AuthController::class, 'updateProfil']);
    Route::post('profil/photo',   [AuthController::class, 'updatePhoto']);
    Route::put('profil/password', [AuthController::class, 'updatePassword']);
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/',                     [NotificationController::class, 'index']);
        Route::get('non-lues',              [NotificationController::class, 'nonLues']);
        Route::post('tout-lire',            [NotificationController::class, 'toutMarquerLu']);
        Route::patch('{notification}/lire', [NotificationController::class, 'marquerLu']);
    });

    // Rendezvous
    Route::get('rendezvous',                  [RendezVousController::class, 'index']);
    Route::get('rendezvous/creneaux',         [RendezVousController::class, 'creneaux']);
    Route::get('rendezvous/{rendezvous}',     [RendezVousController::class, 'show']);
    
    // Disponibilites
    Route::get('disponibilites',              [DisponibiliteController::class, 'index']);
    
    // Patients
    Route::get('patients/{patient}',          [PatientController::class, 'show']);
});

// ── Multi-Role Endpoints ───────────────────────────────────────────────
Route::middleware('role:medecin,secretaire')->group(function () {
    Route::get('patients',                                [PatientController::class, 'index']);
    Route::post('patients',                               [PatientController::class, 'store']);
    Route::patch('rendezvous/{rendezvous}',               [RendezVousController::class, 'update']);
    Route::post('rendezvous/book',                        [RendezVousController::class, 'store']); // Use a specific alias or identical path
});

Route::middleware('role:patient,secretaire')->group(function () {
    Route::patch('rendezvous/{rendezvous}/annuler',       [RendezVousController::class, 'annuler']);
    Route::patch('rendezvous/{rendezvous}/reprendre',     [RendezVousController::class, 'reprendre']); 
    // Allow patients to update their pending appointments
    //Route::patch('rendezvous/{rendezvous}', [RendezVousController::class, 'updatePatient']);
});

Route::middleware('role:patient,medecin')->group(function () {
    Route::get('consultations',                           [ConsultationController::class, 'index']);
    Route::get('consultations/{consultationId}/analyses', [AnalyseController::class, 'index']);
    Route::get('ordonnances',                             [OrdonnanceController::class, 'userOrdonnances']);
    Route::get('analyses',                                [AnalyseController::class, 'userAnalyses']);
    Route::get('analyses/{analyse}/fichier',              [AnalyseController::class, 'fichier']);
    Route::get('prescriptions',                           [PrescriptionController::class, 'index']);
});

// ═════════════════════════════════════════════════════════════════════════════
// FIX #2: Reordered route groups to prevent middleware shadowing
// 
// PROBLEM: The route POST /analyses/prescrire was originally defined in the 
// medecin group AFTER the patient group's wildcard route 
// POST /analyses/{analyse}/fichier. In Laravel, route registration order 
// matters - the patient middleware could intercept requests intended for 
// the medecin endpoint.
//
// SOLUTION: Moved the entire Médecin route group BEFORE the Patient route 
// group, ensuring analyses/prescrire is registered before any wildcard 
// analyses/{...} routes. This guarantees proper middleware assignment.
// ═════════════════════════════════════════════════════════════════════════════

// ── Médecin ───────────────────────────────────────────────────────────
Route::middleware('role:medecin,secretaire')->group(function () {
    Route::patch('patients/{patient}',                    [PatientController::class, 'update']);
});

Route::middleware('role:medecin')->group(function () {
    Route::get('patients/{patientId}/historique',         [ConsultationController::class, 'historique']);

    Route::post('consultations',                          [ConsultationController::class, 'store']);
    Route::get('consultations/{consultation}',              [ConsultationController::class, 'show']);
    Route::patch('consultations/{consultation}',            [ConsultationController::class, 'update']);

    Route::post('ordonnances',                            [OrdonnanceController::class, 'store']);
    Route::get('ordonnances/{ordonnance}',                [OrdonnanceController::class, 'show']);

    // CRITICAL: analyses/prescrire MUST be defined BEFORE any wildcard analyses/{...} routes
    // to prevent Laravel from treating "prescrire" as a parameter value for {analyse}
    Route::post('analyses/prescrire',                       [AnalyseController::class, 'prescrire']);
    
    Route::patch('analyses/{analyse}/annoter',            [AnalyseController::class, 'annoter']);
    Route::delete('analyses/{analyse}',                   [AnalyseController::class, 'destroy']);

    Route::post('disponibilites/sync',                    [DisponibiliteController::class, 'sync']);
    Route::post('disponibilites/bloquer',                 [DisponibiliteController::class, 'bloquer']);

    Route::get('medicaments', [MedicamentController::class, 'index']);     // ?search=xxx
    Route::post('prescriptions', [PrescriptionController::class, 'store']); // pivot ordonnance↔médicament
});

// ── Patient ───────────────────────────────────────────────────────────
Route::middleware('role:patient')->group(function () {
    Route::post('rendezvous',                             [RendezVousController::class, 'store']);
    Route::post('analyses',                               [AnalyseController::class, 'store']);
    Route::patch('patients/{patient}/profil',             [PatientController::class, 'updateProfil']);
    
    // Wildcard route - must come AFTER specific analyses routes
    Route::post('analyses/{analyse}/fichier',             [AnalyseController::class, 'attachFichier']);
});

// ── Admin / Dashboard (Capabilities for Medecin & Secretaire) ────────────
Route::middleware('role:medecin,secretaire')->prefix('admin')->group(function () {
    Route::get('dashboard',                               [AdminController::class, 'dashboard']);
    Route::get('admins',                                  [AdminController::class, 'index']);
});

// ── Admin (Capabilities for Medecin only) ──────────────────────────────
Route::middleware('role:medecin')->prefix('admin')->group(function () {
    Route::post('admins',                                 [AdminController::class, 'store']);
    Route::patch('users/{user}/toggle-active',            [AdminController::class, 'toggleActive']);
});

// ── Patient deletion (Medecin & Secretaire) ──────────────────────────
Route::middleware('role:medecin,secretaire')->group(function () {
    Route::delete('patients/{patient}',                   [PatientController::class, 'destroy']);
});
