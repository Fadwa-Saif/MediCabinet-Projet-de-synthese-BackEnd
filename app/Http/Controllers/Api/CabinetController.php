<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabinet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));

        $query = Cabinet::query()->with('doctor');

        if ($term !== '') {
            $query->where(function ($builder) use ($term) {
                $builder
                    ->where('nom', 'like', '%' . $term . '%')
                    ->orWhere('adresse', 'like', '%' . $term . '%')
                    ->orWhere('ville', 'like', '%' . $term . '%')
                    ->orWhere('specialite', 'like', '%' . $term . '%')
                    ->orWhereHas('doctor', function ($doctorQuery) use ($term) {
                        $doctorQuery
                            ->where('nom', 'like', '%' . $term . '%')
                            ->orWhere('prenom', 'like', '%' . $term . '%');
                    });
            });
        }

        $cabinets = $query
            ->orderBy('nom')
            ->limit(20)
            ->get()
            ->map(function (Cabinet $cabinet) {
                return [
                    'id' => $cabinet->id,
                    'nom' => $cabinet->nom,
                    'adresse' => $cabinet->adresse,
                    'specialite' => $cabinet->specialite,
                    'ville' => $cabinet->ville,
                    'doctor_nom' => trim(($cabinet->doctor?->prenom ?? '') . ' ' . ($cabinet->doctor?->nom ?? '')),
                ];
            })
            ->values();

        return response()->json(['data' => $cabinets], 200);
    }
}