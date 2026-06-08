<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Disponibilite;
use App\Models\RendezVous;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $query = RendezVous::with(['patient.user', 'admin.user']);

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 200));

        $allowedSortBy = ['created_at', 'date_heure'];
        $sortBy = $request->input('sort_by', 'created_at');
        if (!in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc'));
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        if ($request->has('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->has('date')) {
            $query->whereDate('date_heure', $request->input('date'));
        }

        if ($user->isMedecin()) {
            $query->where('admin_id', $user->admin->id);
        } elseif ($user->isPatient()) {
            $query->where('patient_id', $user->patient->id);
        }

        $rendezvous = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($rendezvous, 200);
    }

    public function creneaux(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|integer|exists:admins,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($validated['date']);
        $dayOfWeek = $this->getDayOfWeekFrench($date->dayOfWeek);

        $disponibilite = Disponibilite::where('admin_id', $validated['admin_id'])
            ->where('jour_semaine', $dayOfWeek)
            ->first();

        if (!$disponibilite) {
            $disponibilite = new Disponibilite([
                'admin_id' => $validated['admin_id'],
                'jour_semaine' => $dayOfWeek,
                'heure_debut' => '09:00:00',
                'heure_fin' => '17:00:00',
                'duree_min' => 30,
                'est_disponible' => true,
                'date_exception' => null,
            ]);
        }

        $creneauxDisponibles = $disponibilite->getCreneaux();

        $creneauxPris = RendezVous::where('admin_id', $validated['admin_id'])
            ->whereDate('date_heure', $date)
            ->where('statut', '!=', 'annule')
            ->pluck('date_heure')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $creneaux = array_map(function (string $heure) use ($creneauxPris) {
            return [
                'heure' => $heure,
                'disponible' => !in_array($heure, $creneauxPris, true),
            ];
        }, $creneauxDisponibles);

        return response()->json(['creneaux' => $creneaux], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        $validated = $request->validate([
            'patient_id'    => 'nullable|integer|exists:patients,id',
            'admin_id'      => 'nullable|integer|exists:admins,id',
            'date_heure'    => 'required|date|after:now',
            'motif'         => 'nullable|string|max:255',
            'duree_minutes' => 'required|integer|min:1',
        ]);

        $adminId = $validated['admin_id'] ?? null;

        if (!$adminId) {
            $medecins = Admin::where('role', 'medecin')->orderBy('id')->get();

            if ($medecins->count() !== 1) {
                return response()->json([
                    'message' => 'Le médecin du cabinet doit être précisé.',
                ], 422);
            }

            $adminId = $medecins->first()->id;
        }

        $patient_id = null;

        if ($user->isPatient()) {
            $patient_id = $user->patient->id;
        } else {
            // Medecin or Secretaire must provide patient_id
            if (!$request->has('patient_id')) {
                return response()->json(['message' => 'Le champ patient_id est requis pour les administrateurs.'], 422);
            }
            $patient_id = $validated['patient_id'];
        }

        $conflictingRdv = RendezVous::where('admin_id', $adminId)
            ->where('date_heure', $validated['date_heure'])
            ->exists();

        if ($conflictingRdv) {
            return response()->json(['message' => 'Ce créneau est déjà réservé.'], 409);
        }

        $rendezvous = RendezVous::create([
            'patient_id' => $patient_id,
            'admin_id' => $adminId,
            'date_heure' => $validated['date_heure'],
            'motif' => $validated['motif'] ?? null,
            'duree_minutes' => $validated['duree_minutes'],
            'statut' => 'en_attente',
        ]);

        // Auto-assign patient to doctor if not already assigned
        $patient = RendezVous::find($rendezvous->id)->patient;
        if ($patient) {
            $existingAssignment = $patient->medecins()
                ->where('medecin_id', $adminId)
                ->where('statut', 'actif')
                ->exists();

            if (!$existingAssignment) {
                $patient->medecins()->attach($adminId, [
                    'date_affectation' => now(),
                    'statut' => 'actif',
                ]);
            }
        }

        return response()->json($rendezvous, 201);
    }

    public function update(Request $request, RendezVous $rendezvous): JsonResponse
    {
        $validated = $request->validate([
            'date_heure'    => 'nullable|date|after:now',
            'motif'         => 'nullable|string|max:255',
            'duree_minutes' => 'nullable|integer|min:1',
            'statut'        => 'nullable|in:en_attente,confirme,annule,termine',
        ]);

        $oldStatut = $rendezvous->statut;

        $rendezvous->update($validated);

        // If status changed to 'confirme', send an in-app notification to the patient
        if (isset($validated['statut']) && $validated['statut'] === 'confirme' && $oldStatut !== 'confirme') {
            $patientUserId = $rendezvous->patient?->user?->id ?? null;
            $sender = auth('api')->user();

            if ($patientUserId) {
                Notification::create([
                    'expediteur_id'   => $sender?->id,
                    'destinataire_id' => $patientUserId,
                    'type'            => 'confirmation',
                    'canal'           => 'web',
                    'titre'           => 'Rendez-vous confirmé',
                    'contenu'         => 'Votre rendez-vous du ' . Carbon::parse($rendezvous->date_heure)->format('d/m/Y H:i') . ' a été confirmé.',
                    'lu'              => 0,
                    'date_envoi'      => Carbon::now(),
                ]);
            }
        }

        return response()->json($rendezvous, 200);
    }

    public function show(RendezVous $rendezvous): JsonResponse
    {
        $rendezvous->load(['patient.user', 'admin.user', 'consultation']);

        return response()->json($rendezvous, 200);
    }

    public function annuler(RendezVous $rendezvous): JsonResponse
    {
        if ($rendezvous->statut === 'annule' || $rendezvous->statut === 'termine') {
            return response()->json(['message' => 'Impossible d\'annuler ce rendez-vous.'], 422);
        }

        $rendezvous->annuler();

        return response()->json($rendezvous, 200);
    }

    // ← ADD THIS METHOD
    public function reprendre(RendezVous $rendezvous): JsonResponse
    {
        if ($rendezvous->statut !== 'annule') {
            return response()->json(['message' => 'Seuls les rendez-vous annulés peuvent être repris.'], 422);
        }

        $rendezvous->update(['statut' => 'en_attente']);

        return response()->json([
            'message' => 'Rendez-vous repris avec succès',
            'data' => $rendezvous
        ], 200);
    }

    private function getDayOfWeekFrench(int $dayOfWeek): string
    {
        $days = [
            0 => 'Dim',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mer',
            4 => 'Jeu',
            5 => 'Ven',
            6 => 'Sam',
        ];

        return $days[$dayOfWeek] ?? 'Lun';
    }

    //modifier rendez vous par le patient (seulement si en_attente)
    public function updatePatient(Request $request, RendezVous $rendezvous): JsonResponse
{
    $user = auth('api')->user();
    
    // Authorize: patients can update their own RDV; secretaries may also update
    if ($user->isPatient()) {
        if ($user->patient->id !== $rendezvous->patient_id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
    } elseif (! $user->isSecretaire()) {
        return response()->json(['message' => 'Non autorisé.'], 403);
    }
    
    // Only allow editing if status is 'en_attente'
    if ($rendezvous->statut !== 'en_attente') {
        return response()->json(['message' => 'Seuls les rendez-vous en attente peuvent être modifiés.'], 422);
    }

    $validated = $request->validate([
        'date_heure'    => 'required|date|after:now',
        'motif'         => 'nullable|string|max:255',
        'duree_minutes' => 'required|integer|min:1',
    ]);

    // Check for conflicts (excluding current appointment)
    $conflictingRdv = RendezVous::where('admin_id', $rendezvous->admin_id)
        ->where('date_heure', $validated['date_heure'])
        ->where('id', '!=', $rendezvous->id)
        ->exists();

    if ($conflictingRdv) {
        return response()->json(['message' => 'Ce créneau est déjà réservé.'], 409);
    }

    $rendezvous->update($validated);

    return response()->json([
        'message' => 'Rendez-vous modifié avec succès',
        'data' => $rendezvous
    ], 200);
}
}