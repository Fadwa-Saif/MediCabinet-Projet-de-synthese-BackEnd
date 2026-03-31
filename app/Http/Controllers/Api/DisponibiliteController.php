<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disponibilite;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisponibiliteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $adminId = $request->query('admin_id') ?? auth('api')->user()->admin?->id;

        $disponibilites = Disponibilite::query()
            ->when($adminId, fn ($q) => $q->where('admin_id', $adminId))
            ->orderByRaw("FIELD(jour_semaine, 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam')")
            ->get();

        return response()->json($disponibilites, 200);
    }

    public function sync(Request $request): JsonResponse
    {
        $admin = auth('api')->user()->admin;

        $validated = $request->validate([
            'disponibilites' => 'required|array',
            'disponibilites.*.jour_semaine' => 'required|in:Lun,Mar,Mer,Jeu,Ven,Sam',
            'disponibilites.*.heure_debut' => 'required|date_format:H:i',
            'disponibilites.*.heure_fin' => 'required|date_format:H:i',
            'disponibilites.*.duree_min' => 'nullable|integer|min:1',
            'disponibilites.*.est_disponible' => 'nullable|boolean',
            'disponibilites.*.date_exception' => 'nullable|date',
        ]);

        foreach ($validated['disponibilites'] as $slot) {
            if ($slot['heure_fin'] <= $slot['heure_debut']) {
                return response()->json([
                    'message' => 'Données invalides.',
                    'errors' => [
                        'disponibilites' => ['heure_fin doit être supérieure à heure_debut.'],
                    ],
                ], 422);
            }
        }

        Disponibilite::where('admin_id', $admin->id)->delete();

        foreach ($validated['disponibilites'] as $item) {
            Disponibilite::create([
                'admin_id' => $admin->id,
                'jour_semaine' => $item['jour_semaine'],
                'heure_debut' => $item['heure_debut'],
                'heure_fin' => $item['heure_fin'],
                'duree_min' => $item['duree_min'] ?? 30,
                'est_disponible' => $item['est_disponible'] ?? true,
                'date_exception' => $item['date_exception'] ?? null,
            ]);
        }

        $disponibilites = Disponibilite::where('admin_id', $admin->id)
            ->orderByRaw("FIELD(jour_semaine, 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam')")
            ->get();

        return response()->json($disponibilites, 200);
    }

    public function bloquer(Request $request): JsonResponse
    {
        $admin = auth('api')->user()->admin;

        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date']);
        $jour = $this->mapJourSemaine($date->dayOfWeek);

        Disponibilite::where('admin_id', $admin->id)
            ->where('jour_semaine', $jour)
            ->update([
                'est_disponible' => false,
                'date_exception' => $date->toDateString(),
            ]);

        return response()->json([
            'message' => 'Disponibilité bloquée.',
            'jour_semaine' => $jour,
            'date_exception' => $date->toDateString(),
        ], 200);
    }

    private function mapJourSemaine(int $dayOfWeek): string
    {
        $jours = [
            0 => 'Dim',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mer',
            4 => 'Jeu',
            5 => 'Ven',
            6 => 'Sam',
        ];

        return $jours[$dayOfWeek] ?? 'Lun';
    }
}
