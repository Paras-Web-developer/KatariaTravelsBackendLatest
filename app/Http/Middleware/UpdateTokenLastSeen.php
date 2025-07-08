<?php
// app/Http/Middleware/UpdateTokenLastSeen.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateTokenLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            $token = $user->currentAccessToken();
            if ($token && (!$token->last_seen_at || now()->diffInMinutes($token->last_seen_at) > 1)) {
                $token->forceFill(['last_seen_at' => now()])->save();
            }
        }

        return $next($request);
    }
}
