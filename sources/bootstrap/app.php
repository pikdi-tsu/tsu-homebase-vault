<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Filament\Notifications\Notification;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies('*');
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'scopes' => \Laravel\Passport\Http\Middleware\CheckTokenForAnyScope::class,
            'scope' => \Laravel\Passport\Http\Middleware\CheckToken::class,
            'client' => \Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner::class,
        ]);
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\CheckUserIsActive::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\RecordLastActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handler untuk Database Down (QueryException)
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            return response()->view('errors.index', [
                'message' => 'Sistem sedang dalam perbaikan rutin. Silakan coba lagi nanti.',
                'code' => 503
            ], 503);
        });

        // Handler untuk Akses Ditolak (403 Forbidden)
        $exceptions->render(function (HttpException $e, $request) {
            if (($e->getStatusCode() === 403) && Auth::check() && $request->is('admin/*')) {
                if (!$request->ajax() && !$request->header('X-Livewire')) {
                    return redirect()->route('dashboard');
                }

                Notification::make()
                    ->title('Aksi Ditolak')
                    ->body('Anda tidak memiliki hak akses.')
                    ->danger()
                    ->send();

                return redirect()->back(); // Atau return null
            }
            return null;
        });

        // Handler untuk Invalid Key (LogicException)
        $exceptions->render(function (LogicException $e, Request $request) {
            if (str_contains($e->getMessage(), 'Invalid key supplied')) {
                return response()->view('errors.index', [
                    'message' => 'Akses Aplikasi ini tidak dikenali, laporkan ke PIKDI TSU.',
                    'code' => 500
                ], 500);
            }
            return null;
        });

        // Handler untuk SSO CLIENT ERROR (OAuthServerException)
        $exceptions->render(function (OAuthServerException $e, Request $request) {
            // Cek apakah errornya tipe "invalid_client" (Client ID salah atau Revoked)
            if ($e->getErrorType() === 'invalid_client') {

                // Jika user buka lewat Browser
                if (! $request->expectsJson()) {
                    return response()->view('errors.index', [
                        'message' => 'Akses Aplikasi ini telah DIBEKUKAN atau DICABUT oleh PIKDI TSU.',
                        'code' => 403
                    ], 403);
                }

                // Jika user buka lewat API (JSON)
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Akses Client dibekukan/dicabut.',
                    'status_code' => 401
                ], 401);
            }
            return null;
        });

        // Handler untuk halaman not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->expectsJson() && View::exists('errors.index')) {

                return response()->view('errors.index', [
                    'title' => 'Halaman Tidak Ditemukan',
                    'message' => 'Halaman yang Anda cari tidak ditemukan atau telah dipindahkan.',
                    'code' => 404,
                    'exception' => $e
                ], 404);
            }

            return null;
        });
    })->create();
