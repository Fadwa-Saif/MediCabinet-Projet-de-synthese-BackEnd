<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Analyse;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(): JsonResponse
    {
        $admins = Admin::with('user')->orderBy('role')->get();

        return response()->json($admins, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
            'telephone' => 'nullable|string|max:15',
            'role' => 'required|in:medecin,secretaire',
            'matricule' => 'nullable|string|max:50|unique:admins,matricule',
            'biographie' => 'nullable|string',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'is_active' => true,
        ]);

        $admin = Admin::create([
            'user_id' => $user->id,
            'role' => $validated['role'],
            'matricule' => $validated['matricule'] ?? null,
            'biographie' => $validated['biographie'] ?? null,
        ]);

        $admin->load('user');

        return response()->json($admin, 201);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json($user, 200);
    }

    public function dashboard(): JsonResponse
    {
        $data = [
            'total_patients' => Patient::count(),
            'total_medecins' => Admin::where('role', 'medecin')->count(),
            'rdv_aujourd_hui' => RendezVous::whereDate('date_heure', now()->toDateString())->count(),
            'rdv_en_attente' => RendezVous::where('statut', 'en_attente')->count(),
            'consultations_mois' => Consultation::whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->count(),
            'analyses_en_attente' => Analyse::whereNull('date_resultat')->count(),
        ];

        return response()->json($data, 200);
    }
}
