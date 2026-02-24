<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class   RecordLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek user login
        if (Auth::check()) {
            $user = Auth::user();

            // Cek kolom last_seen_at kosong atau lewat 2 menit
            if ($user->last_seen_at === null || now()->diffInMinutes($user->last_seen_at) > 2) {

                // Fungsi update()
                $user->update([
                    'last_seen_at' => now(),
                ]);
            }
        }

        return $next($request);
    }
}
