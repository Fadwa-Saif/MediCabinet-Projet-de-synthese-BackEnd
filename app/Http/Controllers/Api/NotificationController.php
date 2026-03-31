<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $notifications = Notification::where('destinataire_id', $user->id)
            ->orderByDesc('date_envoi')
            ->paginate(20);

        return response()->json($notifications, 200);
    }

    public function nonLues(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $count = Notification::where('destinataire_id', $user->id)
            ->where('lu', false)
            ->count();

        return response()->json(['count' => $count], 200);
    }

    public function marquerLu(Notification $notification): JsonResponse
    {
        $user = auth('api')->user();

        if ($notification->destinataire_id !== $user->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $notification->marquerLu();

        return response()->json($notification->fresh(), 200);
    }

    public function toutMarquerLu(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        Notification::where('destinataire_id', $user->id)->update(['lu' => true]);

        return response()->json(['message' => 'Toutes les notifications sont marquées comme lues.'], 200);
    }
}
