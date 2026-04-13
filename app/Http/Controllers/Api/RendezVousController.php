<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disponibilite;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $query = RendezVous::with(['patient.user', 'admin.user']);

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

        $rendezvous = $query->orderBy('date_heure', 'ASC')->paginate(20);

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
            return response()->json(['creneaux' => []], 200);
        }

        $creneaux = $disponibilite->getCreneaux();

        $pris = RendezVous::where('admin_id', $validated['admin_id'])
            ->whereDate('date_heure', $date)
            ->pluck('date_heure')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $creneaux = array_diff($creneaux, $pris);

        return response()->json(['creneaux' => array_values($creneaux)], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Utilisateur n\'est pas un patient.'], 403);
        }

        $validated = $request->validate([
            'admin_id'      => 'required|integer|exists:admins,id',
            'date_heure'    => 'required|date|after:now',
            'motif'         => 'nullable|string|max:255',
            'duree_minutes' => 'required|integer|min:1',
        ]);

        $conflictingRdv = RendezVous::where('admin_id', $validated['admin_id'])
            ->where('date_heure', $validated['date_heure'])
            ->exists();

        if ($conflictingRdv) {
            return response()->json(['message' => 'Ce créneau est déjà réservé.'], 409);
        }

        $rendezvous = RendezVous::create([
            'patient_id' => $patient->id,
            'admin_id' => $validated['admin_id'],
            'date_heure' => $validated['date_heure'],
            'motif' => $validated['motif'] ?? null,
            'duree_minutes' => $validated['duree_minutes'],
            'statut' => 'en_attente',
        ]);

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

        $rendezvous->update($validated);

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
    
    // Ensure the user owns this appointment
    if ($user->patient->id !== $rendezvous->patient_id) {
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