<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKeyHeader = $request->header('X-API-KEY');

        if (!$apiKeyHeader) {
            return response()->json([
                'success' => false,
                'message' => 'API Key is missing. Please provide it in the X-API-KEY header.',
            ], 401);
        }

        $hashedKey = hash('sha256', $apiKeyHeader);
        $apiKey = \App\Models\ApiKey::where('key', '=', $hashedKey)->first();

        if (!$apiKey || !$apiKey->user || $apiKey->user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API Key.',
            ], 401);
        }

        // Update last used timestamp
        $apiKey->update(['last_used_at' => now()]);

        // Authenticate the user for this request
        \Illuminate\Support\Facades\Auth::login($apiKey->user);

        return $next($request);
    }
}
