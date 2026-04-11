<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Patient::with('user');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $patients = $query->paginate(15);

        return response()->json($patients, 200);
    }

    public function show(Patient $patient): JsonResponse
    {
        $patient->load([
            'user',
            'rendezVous.admin.user',
            'consultations.admin.user',
            'consultations.ordonnances.medicaments',
            'consultations.analyses',
            'consultations.radiologies',
        ]);

        return response()->json($patient, 200);
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'date_naissance' => 'nullable|date',
            'cin' => 'nullable|string|max:10',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'groupe_sanguin' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'antecedents' => 'nullable|string',
            'antecedents_familiaux' => 'nullable|string',
            'allergies' => 'nullable|string',
            'poids_kg' => 'nullable|numeric|min:0',
            'taille_cm' => 'nullable|integer|min:0',
            'traitement_en_cours' => 'nullable|string',
        ]);

        $validated['dossier_updated_at'] = now();

        $patient->update($validated);

        return response()->json($patient, 200);
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->user->delete();

        return response()->json(['message' => 'Patient supprimé avec succès.'], 200);
    }

    public function updateProfil(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'nullable|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'telephone' => 'nullable|string|max:15',
            'photo_profil' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo_profil')) {
            $photoPath = $request->file('photo_profil')->store('photos', 'public');
            $validated['photo_profil'] = $photoPath;
        }

        if (isset($validated['nom'])) {
            $patient->user->update(['nom' => $validated['nom']]);
        }
        if (isset($validated['prenom'])) {
            $patient->user->update(['prenom' => $validated['prenom']]);
        }
        if (isset($validated['telephone'])) {
            $patient->user->update(['telephone' => $validated['telephone']]);
        }
        if (isset($validated['photo_profil'])) {
            $patient->user->update(['photo_profil' => $validated['photo_profil']]);
        }

        $patient->user->refresh();

        return response()->json(['user' => $patient->user], 200);
    }
}
