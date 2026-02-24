<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', static function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', static function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Unified Login Logic
        Fortify::authenticateUsing(static function (Request $request) {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // 1. Cek Tabel Dosen/Tendik
            $dosenTendik = UserDosenTendik::query()->where('email', $request->email)->first();
            if ($dosenTendik && Hash::check($request->password, $dosenTendik->password)) {
                session(['auth_type' => 'dosen-tendik']);
                return $dosenTendik;
            }

            // 2. Cek Mahasiswa
            $mahasiswa = UserMahasiswa::query()->where('email', $request->email)->first();
            if ($mahasiswa && Hash::check($request->password, $mahasiswa->password)) {
                session(['auth_type' => 'mahasiswa']);
                return $mahasiswa;
            }

            return null;
        });

        $this->app->singleton(LoginResponse::class, function ($app) {
            return new class implements LoginResponse {
                public function toResponse($request): \Illuminate\Http\RedirectResponse
                {
                    $user = auth()->user();
                    $home = config('fortify.home');

                    if ($request->session()->has('url.intended')) {
                        return redirect()->intended($home);
                    }

                    if ($user instanceof UserDosenTendik) {
                        if ($user->hasRole('admin|super admin')) {
                            return redirect()->intended('/admin');
                        }

                        // Opsional: Logout atau ke halaman user biasa
                        // Auth::logout();
                        // return redirect('/login')->with('error', 'Anda bukan Admin.');
                        return redirect('/dashboard');
                    }

                    if ($user instanceof UserMahasiswa) {
                        return redirect('/dashboard');
                    }

//                    $request->session()->flash('error', 'Anda tidak memiliki hak akses untuk masuk ke sistem ini.');

                    return redirect()->intended(config('fortify.home'));
                }
            };
        });
    }
}
