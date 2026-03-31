<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnalyseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultation_id' => 'nullable|integer|exists:consultations,id',
            'centre_id' => 'nullable|integer|exists:centres_radio_analyse,id',
            'type_analyse' => 'nullable|in:biologie,autre',
            'date_analyse' => 'nullable|date',
            'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('fichier')->store('analyses', 'public');

        $analyse = Analyse::create([
            'consultation_id' => $validated['consultation_id'] ?? null,
            'centre_id' => $validated['centre_id'] ?? null,
            'type_analyse' => $validated['type_analyse'] ?? null,
            'date_analyse' => $validated['date_analyse'] ?? null,
            'fichier' => $path,
        ]);

        return response()->json($analyse, 201);
    }

    public function index(int $consultationId): JsonResponse
    {
        $analyses = Analyse::with('centre')
            ->where('consultation_id', $consultationId)
            ->orderByDesc('date_analyse')
            ->get();

        return response()->json($analyses, 200);
    }

    public function annoter(Request $request, Analyse $analyse): JsonResponse
    {
        $validated = $request->validate([
            'commentaire_medecin' => 'required|string',
            'date_resultat' => 'nullable|date',
        ]);

        $analyse->update($validated);

        return response()->json($analyse, 200);
    }

    public function destroy(Analyse $analyse): JsonResponse
    {
        if (!empty($analyse->fichier)) {
            Storage::disk('public')->delete($analyse->fichier);
        }

        $analyse->delete();

        return response()->json(['message' => 'Analyse supprimée avec succès.'], 200);
    }
}
