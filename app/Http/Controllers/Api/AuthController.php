<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Cabinet;
use App\Models\Patient;
use App\Models\SecretaryMedecin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'nullable|string|in:patient,medecin,secretaire,doctor,secretary',
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:15',
            'date_naissance' => 'nullable|date',
            'cin' => 'nullable|string|max:10|unique:patients',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'specialite' => 'nullable|string|max:120',
            // `cabinet_id` or `medecin_id` is required for secretaries, but not both.
            'cabinet_id' => 'nullable|exists:cabinets,id|required_if:role,secretaire|required_without:medecin_id',
            'medecin_id' => 'nullable|exists:users,id|required_if:role,secretaire|required_without:cabinet_id',
            'cabinet' => 'nullable|array',
            'cabinet.nom' => 'nullable|string|max:150',
            'cabinet.adresse' => 'nullable|string',
            'cabinet.ville' => 'nullable|string|max:100',
            'cabinet.specialite' => 'nullable|string|max:120',
        ]);

        $role = $this->normalizeRole($validated['role'] ?? 'patient');

        return DB::transaction(function () use ($validated, $role) {
            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telephone' => $validated['telephone'] ?? null,
                'is_active' => 1,
            ]);

            $profile = null;

            if ($role === 'patient') {
                Patient::create([
                    'user_id' => $user->id,
                    'date_naissance' => $validated['date_naissance'] ?? null,
                    'cin' => $validated['cin'] ?? null,
                    'adresse' => $validated['adresse'] ?? null,
                    'ville' => $validated['ville'] ?? null,
                    'date_creation_dossier' => now()->toDateString(),
                ]);
                $user->load('patient');
                $profile = $user->patient;
            } elseif ($role === 'medecin') {
                $cabinetData = $validated['cabinet'] ?? null;

                if (!$cabinetData) {
                    throw ValidationException::withMessages([
                        'cabinet' => 'Les informations du cabinet sont obligatoires pour un docteur.',
                    ]);
                }

                Admin::create([
                    'user_id' => $user->id,
                    'role' => 'medecin',
                    'matricule' => null,
                    'biographie' => null,
                ]);

                $cabinet = Cabinet::create([
                    'nom' => $cabinetData['nom'],
                    'adresse' => $cabinetData['adresse'],
                    'ville' => $cabinetData['ville'] ?? null,
                    'specialite' => $cabinetData['specialite'] ?? ($validated['specialite'] ?? 'Médecine générale'),
                    'docteur_id' => $user->id,
                ]);

                $user->update(['cabinet_id' => $cabinet->id]);
                $user->load(['admin', 'cabinet']);
                $profile = $user->admin;
            } elseif ($role === 'secretaire') {
                $medecin = null;

                if (!empty($validated['medecin_id'])) {
                    $medecin = User::with('admin', 'cabinet')->find($validated['medecin_id']);
                } elseif (!empty($validated['cabinet_id'])) {
                    $cabinet = Cabinet::with('doctor')->find($validated['cabinet_id']);
                    if (!$cabinet) {
                        throw ValidationException::withMessages([
                            'cabinet_id' => 'Cabinet introuvable.',
                        ]);
                    }
                    $medecin = $cabinet->doctor;
                }

                if (!$medecin || $medecin->admin?->role !== 'medecin') {
                    throw ValidationException::withMessages([
                        'medecin_id' => 'Médecin introuvable ou invalide.',
                    ]);
                }

                Admin::create([
                    'user_id' => $user->id,
                    'role' => 'secretaire',
                    'matricule' => null,
                    'biographie' => null,
                ]);

                $user->update(['cabinet_id' => $medecin->cabinet?->id ?? $validated['cabinet_id'] ?? null]);

                SecretaryMedecin::create([
                    'secretary_id' => $user->id,
                    'medecin_id' => $medecin->id,
                    'statut' => 'en_attente',
                ]);

                $user->load(['admin', 'cabinet']);
                $profile = $user->admin;
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => $user,
                'profile' => $profile,
                'role' => $role,
                'cabinet' => $user->cabinet ?? null,
                'secretary_request' => $user->secretaryRequest ? [
                    'id' => $user->secretaryRequest->id,
                    'statut' => $user->secretaryRequest->statut,
                    'date_demande' => $user->secretaryRequest->date_demande,
                    'date_decision' => $user->secretaryRequest->date_decision,
                    'motif_refus' => $user->secretaryRequest->motif_refus,
                ] : null,
            ], 201);
        });
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'nullable|string|in:patient,medecin,secretaire,doctor,secretary',
        ]);

        $token = auth('api')->attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        if (!$token) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        $user = auth('api')->user();

        if ($user->is_active === false) {
            auth('api')->logout();
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        $user->load(['admin', 'patient', 'cabinet', 'secretaryRequest.medecin']);

        if ($user->isSecretaire()) {
            if ($user->secretaryRequest?->statut === 'refusee') {
                auth('api')->logout();

                return response()->json([
                    'status' => 'refusee',
                    'message' => 'Votre demande a été refusée.',
                ], 403);
            }

            if ($user->secretaryRequest?->statut === 'en_attente') {
                auth('api')->logout();

                return response()->json([
                    'status' => 'en_attente',
                    'redirect' => '/en-attente',
                    'secretary_request' => [
                        'id' => $user->secretaryRequest->id,
                        'statut' => $user->secretaryRequest->statut,
                        'date_demande' => $user->secretaryRequest->date_demande,
                        'medecin' => $user->secretaryRequest->medecin ? [
                            'id' => $user->secretaryRequest->medecin->id,
                            'nom' => $user->secretaryRequest->medecin->nom,
                            'prenom' => $user->secretaryRequest->medecin->prenom,
                            'email' => $user->secretaryRequest->medecin->email,
                        ] : null,
                    ],
                ], 200);
            }
        }

        $profile = $user->admin ?? $user->patient;
        $role = $user->admin?->role ?? 'patient';

        $requestedRole = isset($validated['role']) ? $this->normalizeRole($validated['role']) : null;

        if ($requestedRole && $requestedRole !== $role) {
            auth('api')->logout();

            return response()->json([
                'message' => 'Rôle incorrect pour ce compte.',
            ], 403);
        }

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
            'profile' => $profile,
            'role' => $role,
            'cabinet' => $user->cabinet,
            'secretary_request' => $user->secretaryRequest ? [
                'id' => $user->secretaryRequest->id,
                'statut' => $user->secretaryRequest->statut,
                'date_demande' => $user->secretaryRequest->date_demande,
                'date_decision' => $user->secretaryRequest->date_decision,
                'motif_refus' => $user->secretaryRequest->motif_refus,
                'medecin' => $user->secretaryRequest->medecin ? [
                    'id' => $user->secretaryRequest->medecin->id,
                    'nom' => $user->secretaryRequest->medecin->nom,
                    'prenom' => $user->secretaryRequest->medecin->prenom,
                    'email' => $user->secretaryRequest->medecin->email,
                ] : null,
            ] : null,
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Déconnecté avec succès.'], 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = auth('api')->refresh();

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = auth('api')->user()->load(['admin', 'patient', 'cabinet', 'secretaryRequest.medecin']);

        $role = $user->admin?->role ?? 'patient';

        return response()->json([
            'user' => $user,
            'role' => $role,
            'cabinet' => $user->cabinet,
            'secretary_request' => $user->secretaryRequest ? [
                'id' => $user->secretaryRequest->id,
                'statut' => $user->secretaryRequest->statut,
                'date_demande' => $user->secretaryRequest->date_demande,
                'date_decision' => $user->secretaryRequest->date_decision,
                'motif_refus' => $user->secretaryRequest->motif_refus,
                'medecin' => $user->secretaryRequest->medecin ? [
                    'id' => $user->secretaryRequest->medecin->id,
                    'nom' => $user->secretaryRequest->medecin->nom,
                    'prenom' => $user->secretaryRequest->medecin->prenom,
                    'email' => $user->secretaryRequest->medecin->email,
                ] : null,
            ] : null,
        ], 200);
    }

    public function mySecretaryRequest(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user->isSecretaire()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $secretaryRequest = $user->load('secretaryRequest.medecin')->secretaryRequest;

        if (!$secretaryRequest) {
            return response()->json(['message' => 'Demande introuvable.'], 404);
        }

        return response()->json([
            'secretary_request' => [
                'id' => $secretaryRequest->id,
                'statut' => $secretaryRequest->statut,
                'date_demande' => $secretaryRequest->date_demande,
                'date_decision' => $secretaryRequest->date_decision,
                'motif_refus' => $secretaryRequest->motif_refus,
                'medecin' => $secretaryRequest->medecin ? [
                    'id' => $secretaryRequest->medecin->id,
                    'nom' => $secretaryRequest->medecin->nom,
                    'prenom' => $secretaryRequest->medecin->prenom,
                    'email' => $secretaryRequest->medecin->email,
                ] : null,
            ],
        ], 200);
    }

    private function normalizeRole(?string $role): string
    {
        return match ($role) {
            'doctor' => 'medecin',
            'secretary' => 'secretaire',
            default => $role ?? 'patient',
        };
    }

    public function profil(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'telephone' => $user->telephone,
                'photo_profil' => $user->photo_profil
                    ? url('storage/' . ltrim($user->photo_profil, '/'))
                    : null,
            ],
        ], 200);
    }

    public function updateProfil(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'telephone' => 'nullable|string|max:15',
        ]);

        $user->update($validated);
        $user->refresh();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'data' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'telephone' => $user->telephone,
                'photo_profil' => $user->photo_profil
                    ? url('storage/' . ltrim($user->photo_profil, '/'))
                    : null,
            ],
        ], 200);
    }

    public function updatePhoto(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'photo_profil' => 'required|image|max:2048',
        ]);

        if ($user->photo_profil) {
            Storage::disk('public')->delete($user->photo_profil);
        }

        $path = $request->file('photo_profil')->store('photos', 'public');
        $user->update(['photo_profil' => $path]);

        return response()->json([
            'message' => 'Photo de profil mise à jour avec succès.',
            'photo_profil' => url('storage/' . ltrim($path, '/')),
            'data' => [
                'photo_profil' => url('storage/' . ltrim($path, '/')),
            ],
        ], 200);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Mot de passe actuel incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
        ], 200);
    }
}
