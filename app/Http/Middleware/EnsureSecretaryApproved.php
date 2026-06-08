<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class EnsureSecretaryApproved
{
    public function handle(Request $request, Closure $next): JsonResponse|\Illuminate\Http\Response
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

        if ($user && $user->isSecretaire()) {
            if ($user->secretaryRequest?->statut !== 'approuvee') {
                return response()->json([
                    'message' => 'Votre compte secrétaire n\'est pas encore approuvé.',
                    'status' => $user->secretaryRequest?->statut,
                ], 403);
            }
        }

        return $next($request);
    }
}
