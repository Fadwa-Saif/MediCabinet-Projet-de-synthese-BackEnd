<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabinet;
use App\Models\Disponibilite;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));
        $specialite = trim((string) $request->query('specialite', ''));
        $ville = trim((string) $request->query('ville', ''));

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

        if ($specialite !== '' && strtolower($specialite) !== 'toutes') {
            $query->where('specialite', 'like', '%' . $specialite . '%');
        }

        if ($ville !== '') {
            $query->where('ville', 'like', '%' . $ville . '%');
        }

        $cabinets = $query
            ->orderBy('nom')
            ->get()
            ->map(function (Cabinet $cabinet) {
                return [
                    'id' => $cabinet->id,
                    'nom' => $cabinet->nom,
                    'adresse' => $cabinet->adresse,
                    'specialite' => $cabinet->specialite,
                    'ville' => $cabinet->ville,
                    'doctor' => [
                        'id' => $cabinet->doctor?->id,
                        'prenom' => $cabinet->doctor?->prenom,
                        'nom' => $cabinet->doctor?->nom,
                        'photo' => $cabinet->doctor?->photo_profil,
                    ],
                ];
            })
            ->values();

        return response()->json($cabinets, 200);
    }

    public function disponibilites(Cabinet $cabinet, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date']);
        $dayOfWeek = [0 => 'Dim', 1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam'][$date->dayOfWeek] ?? 'Lun';
        $adminId = $cabinet->doctor?->admin?->id;

        if (!$adminId) {
            return response()->json([], 200);
        }

        $disponibilite = Disponibilite::where('admin_id', $adminId)
            ->where('jour_semaine', $dayOfWeek)
            ->first();

        if (!$disponibilite) {
            return response()->json([], 200);
        }

        $slots = $disponibilite->getCreneaux();
        $takenSlots = RendezVous::where('admin_id', $adminId)
            ->whereDate('date_heure', $date)
            ->where('statut', '!=', 'annule')
            ->pluck('date_heure')
            ->map(fn ($slot) => Carbon::parse($slot)->format('H:i'))
            ->all();

        $available = array_values(array_filter($slots, fn (string $slot) => !in_array($slot, $takenSlots, true)));

        return response()->json($available, 200);
    }
}