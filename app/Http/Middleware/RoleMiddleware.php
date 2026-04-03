<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response|JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expiré.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalide.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if ($user === null) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        foreach ($roles as $role) {
            $hasRole = match ($role) {
                'patient'    => $user->isPatient(),
                'medecin'    => $user->isMedecin(),
                'secretaire' => $user->isSecretaire(),
                'admin'      => $user->isAdmin(),
                default      => false,
            };

            if ($hasRole) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Accès refusé.'], 403);
    }
}