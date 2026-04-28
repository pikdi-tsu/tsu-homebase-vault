<?php

use App\Http\Controllers\RemoteAccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\HealthStatusPage;
use Illuminate\Support\Facades\Session;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use App\Http\Controllers\Auth\CustomPasswordResetLinkController;
use App\Http\Controllers\Auth\CustomNewPasswordController;
use App\Http\Controllers\Api\V1\SsoController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    //
});

Route::middleware(['guest'])->group(function () {
    Route::get('/', static fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', static function () {
        // Dapatkan UUID dari batch pemeriksaan terakhir
        $latestBatch = HealthCheckResultHistoryItem::query()->latest()->value('batch');

        // Dapatkan semua hasil pemeriksaan dari batch terakhir tersebut
        $latestChecks = HealthCheckResultHistoryItem::query()->where('batch', $latestBatch)->get();

        // Cek status 'failed' di dalam batch terakhir
        $isSystemOk = !$latestChecks->contains(fn ($check) => $check->status !== 'ok');

        return view('dashboard', ['isSystemOk' => $isSystemOk]);
    })->name('dashboard');
    Route::get('/admin/{any}', static function () {
        // Skenario 1: Jika user belum login (guest)
        if (Auth::guest()) {
            return redirect()->route('login');
        }
        // Skenario 2: Jika user sudah login (tapi bukan admin)
        return redirect()->route('dashboard');
    })->where('any', '.*')->name('admin.fallback');
    Route::post('/forgot-password', [CustomPasswordResetLinkController::class, 'store'])->name('password.email');
    Route::post('/reset-password', [CustomNewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/oauth/authorize', static function (Request $request) {
        // Cek client reevoked
        $clientId = $request->query('client_id');
        $client = DB::table('oauth_clients')->where('id', $clientId)->first();
        if (!$client || $client->revoked) {
            return response()->view('errors.index', [
                'message' => 'Maaf, Akses Aplikasi ini telah DIBEKUKAN atau DICABUT oleh PIKDI TSU.',
                'code' => 403
            ], 403);
        }

        // Cek user disabled
        $user = $request->user();
        if ($user && !$user->isactive) {

            return response()->view('errors.index', [
                'title' => 'Akun Non-Aktif',
                'message' => 'Mohon maaf, Akun TSU Anda saat ini sedang DINONAKTIFKAN. Silakan hubungi Bagian SDM/PIKDI.',
                'code' => 403
            ], 403);
        }

        // "Hidupkan" Controller-nya (Resolve Instance)
        $controller = app(\Laravel\Passport\Http\Controllers\AuthorizationController::class);

        // Laravel otomatis mengisikan parameter yang kurang (Request, Response, dll)
        return app()->call([$controller, 'authorize']);

    });
    Route::get('/sso/logout', static function () {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
        return redirect('/');
    })->name('sso.logout');
    // Route "Jumper"
    Route::get('/status-sistem', HealthStatusPage::class)->name('health.status');
    Route::get('/jump-to-module/{module}', RemoteAccessController::class)->name('jump-to-module');
});

// Authorization Grant Test Route
//Route::get('/test-callback', static function (Request $request) {
//    $code = $request->code ?? '';
//
//    if (!$code) {
//        return response()->json(['error' => 'Kode tidak ditemukan! Login gagal.'], 400);
//    }
//
//    $response = '';
//
//    try {
//        $response = \Illuminate\Support\Facades\Http::withoutVerifying()->asForm()->post(config('app.url') . '/oauth/token', [
//            'grant_type' => 'authorization_code',
//            'client_id' => config('passport.authorization_grant_client.id'),
//            'client_secret' => config('passport.authorization_grant_client.secret'),
//            'redirect_uri' => config('app.url') . '/test-callback',
//            'code' => $code,
//        ]);
//    } catch (\Illuminate\Http\Client\ConnectionException $e) {}
//
//    return $response->json();
//});

// --- Rute untuk Testing Halaman Error ---
//Route::get('/404', function () {
//    abort(404);
//})->name('404');
//
//Route::get('/403', function () {
//    abort(403, 'Akses Ditolak.');
//})->name('403');
//
//Route::get('/500', function () {
//    abort(500, 'Terjadi Masalah Internal.');
//})->name('500');
