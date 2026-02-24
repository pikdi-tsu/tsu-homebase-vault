<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuthorizationController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Middleware\LogSsoActivity;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SsoController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

//Route::get('/v1/client/users-dosen-tendik', [UserDosenTendikController::class, 'index'])
//    ->middleware('client');
//Route::get('/v1/user/users-dosen-tendik', [UserDosenTendikController::class, 'index'])
//    ->middleware('auth:api');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Endpoints Otentikasi (Publik) ---
Route::prefix('v1')->group(function () {

    // API PUBLIK
    Route::prefix('auth')->group(function () {
        Route::post('/login/dosen-tendik', [AuthController::class, 'loginDosenTendik']);
        Route::post('/login/mahasiswa', [AuthController::class, 'loginMahasiswa']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
//        Route::post('/password/send-link', [AuthController::class, 'sendResetLink']);
//        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // API USER (PERSONAL ACCESS)
    Route::middleware(['auth:api, api2', LogSsoActivity::class])->group(function () {
        // Auth Management
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'getMe']);

        // Profile Management
        Route::prefix('profile')->group(function() {
            Route::get('/', [UserProfileController::class, 'show']);
            Route::put('/', [UserProfileController::class, 'update']);
            Route::post('/change-photo', [UserProfileController::class, 'updatePhoto']);
            Route::post('/change-password', [UserProfileController::class, 'changePassword']);
        });
    });

    // API SERVER-TO-SERVER (CLIENT CREDENTIALS)
    Route::middleware(['client'])->group(function () {

        // Sync Users
        Route::match(['get', 'post'],'users/sync', [UserController::class, 'getUsers']);

        // Sync Roles (General Scope)
        Route::get('roles/sync-list', [RoleController::class, 'syncList']);

        // Manajemen User & Permission (High Level Scope)
        Route::middleware(['scopes:system:user:view'])->group(function () {
            Route::apiResource('users', UserController::class);
            Route::get('/users/{id}/permissions', [AuthorizationController::class, 'getUserPermissions']);
        });

        // Contoh: Group untuk manajemen Role/Permission secara CRUD (Admin Level)
        Route::middleware(['scopes:system:role:create'])->group(function () {
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('permissions', PermissionController::class);
        });

    });
});
