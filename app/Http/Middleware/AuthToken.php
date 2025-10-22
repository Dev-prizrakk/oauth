<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;

class AuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $tokenString = $request->bearerToken();

        if (!$tokenString) {
            return response()->json(['error' => 'Token missing'], 401);
        }

        $token = ApiToken::where('token', $tokenString)->first();

        if (!$token || $token->expires_at?->isPast()) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        $request->merge(['user' => $token->user]);
        return $next($request);
    }
}
