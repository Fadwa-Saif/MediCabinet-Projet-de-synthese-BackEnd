<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response|JsonResponse
    {
        $user = auth('api')->user();

        if ($user === null) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        foreach ($roles as $role) {
            $hasRole = match ($role) {
                'patient' => $user->isPatient(),
                'medecin' => $user->isMedecin(),
                'secretaire' => $user->isSecretaire(),
                'admin' => $user->isAdmin(),
                default => false,
            };

            if ($hasRole) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Accès refusé.'], 403);
    }
}
