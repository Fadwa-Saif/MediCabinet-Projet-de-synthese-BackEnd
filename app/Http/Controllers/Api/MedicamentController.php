<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicamentController extends Controller
{
    /**
     * Get all medicaments with optional search filter
     */
    public function index(Request $request): JsonResponse
    {
        $query = Medicament::query();

        // Filter by search term (nom or nom_generique)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('nom_generique', 'like', "%{$search}%");
            });
        }

        $medicaments = $query->select('id', 'nom', 'nom_generique', 'forme', 'description')
            ->limit(50)
            ->get();

        return response()->json($medicaments, 200);
    }
}
