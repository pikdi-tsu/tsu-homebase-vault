<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login DAN statusnya TIDAK aktif
        if (Auth::check() && !Auth::user()->isactive) {
            // Opsional: Cabut tokennya jika ada
            // Auth::user()->token()->revoke();

            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.'
            ], 403); // 403 Forbidden
        }

        return $next($request);
    }
}
