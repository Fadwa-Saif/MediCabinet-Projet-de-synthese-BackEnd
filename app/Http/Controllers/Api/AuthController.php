<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:15',
            'date_naissance' => 'nullable|date',
            'cin' => 'nullable|string|max:10|unique:patients',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'is_active' => 1,
        ]);

        Patient::create([
            'user_id' => $user->id,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'cin' => $validated['cin'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'ville' => $validated['ville'] ?? null,
            'date_creation_dossier' => now()->toDateString(),
        ]);

        $token = JWTAuth::fromUser($user);
        $user->load('patient');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
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

        $user->load(['admin', 'patient']);

        $profile = $user->admin ?? $user->patient;
        $role = $user->admin?->role ?? 'patient';

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
            'profile' => $profile,
            'role' => $role,
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
        $user = auth('api')->user()->load(['admin', 'patient']);

        $role = $user->admin?->role ?? 'patient';

        return response()->json([
            'user' => $user,
            'role' => $role,
        ], 200);
    }
}
