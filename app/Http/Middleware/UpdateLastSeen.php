<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update([
                'last_seen_at' => now(),
                'user_login' => 1 // Optional: mark user as online
            ]);
        }

        return $next($request);
    }
}
