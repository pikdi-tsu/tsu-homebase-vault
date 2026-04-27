<?php

namespace App\Providers;

use App\Extensions\SmartUserProvider;
use App\Health\IndonesianWindowsDiskSpaceCheck;
use App\Health\PassportKeysCheck;
use App\Http\Responses\LoginResponse;
use App\Models\Passport\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Listeners\CheckUserRoleAfterLogin;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->usePublicPath(base_path('../'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(static function ($user, $ability) {
            return $user->hasRole('super admin') ? true : null;
        });

        Auth::provider('smart_eloquent', static function ($app, array $config) {
            return new SmartUserProvider($app['hash'], $config['model']);
        });

        Passport::useClientModel(Client::class);
        Passport::authorizationView('vendor.passport.authorize');
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addHours(8)); // Access Token berlaku 8 jam
        Passport::refreshTokensExpireIn(now()->addDays(30)); // Refresh Token berlaku 30 hari
        Passport::personalAccessTokensExpireIn(now()->addMonths(6)); // Token pribadi berlaku 6 bulan

        if (! $this->app->runningInConsole()) {
            if (Schema::hasTable('permissions')) {
                $permissions = Permission::all()->pluck('name')->toArray();
                $scopes = array_fill_keys($permissions, 'Izin dinamis dari database');
                Passport::tokensCan($scopes);
            }
        }

        Event::listen(
            Login::class,
            CheckUserRoleAfterLogin::class
        );

        FilamentAsset::register([
            Js::make('custom-filament', __DIR__ . '/../../resources/js/custom-filament.js'),
        ]);

        Health::checks([
            ScheduleCheck::new(),
            DatabaseCheck::new(),
            CacheCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
//            UsedDiskSpaceCheck::new(),
            IndonesianWindowsDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(60)
                ->failWhenUsedSpaceIsAbovePercentage(85),
            PassportKeysCheck::new(),
        ]);
    }
}
