<?php

namespace App\Http\Middleware;

use App\Models\Module;
use App\Models\ModuleAccessLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogSsoActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // CEK REQUEST PUNYA USER & TOKEN
        if ($request->user() && $request->user()->token()) {

            $user = $request->user();
            $token = $request->user()->token();

            // AMBIL CLIENT ID DARI TOKEN TERSEBUT
            $clientId = $token->client_id;

            if ($clientId) {
                // CARI MODULE YANG PUNYA CLIENT ID ITU
                $module = Module::query()->where('passport_client_id', $clientId)->first();

                if ($module) {
                    $method = 'SSO';

                    $recentLog = ModuleAccessLog::query()
                        ->where('module_id', $module->id)
                        ->where('target_user_id', $user->id)
                        ->where('target_user_type', $user->getMorphClass())
                        ->where('accessed_at', '>=', now()->subMinutes(5))
                        ->latest('accessed_at')
                        ->first();

                    if ($recentLog) {
                        $recentLog->update([
                            'accessed_at' => now(),
                            'ip_address'  => $request->ip(),
                            'login_method' => $method,
                        ]);
                    } else {
                        ModuleAccessLog::query()->create([
                            'module_id'        => $module->id,
                            'admin_id'         => $user->id,
                            'target_user_id'   => $user->id,
                            'target_user_type' => $user->getMorphClass(),
                            'accessed_at'      => now(),
                            'ip_address'       => $request->ip(),
                            'user_agent'       => $request->userAgent(),
                            'login_method'     => $method,
                        ]);
                    }
                }
            }
        }

        return $next($request);
    }
}
