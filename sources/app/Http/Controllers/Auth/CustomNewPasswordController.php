<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Contracts\PasswordResetResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse;

class CustomNewPasswordController extends NewPasswordController
{
    public function store(Request $request): Responsable
    {
        // 1. Validasi input
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Cek Token di database
        $resetRecord = PasswordResetToken::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        // 3. Handle Token Tidak Valid atau Kedaluwarsa
        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            return app(FailedPasswordResetResponse::class, ['status' => __('passwords.token')]);
        }

        // 4. LOGIKA KUSTOM-MU
        $user = UserMahasiswa::where('email', $request->email)->first();
        if (!$user) {
            $user = UserDosenTendik::where('email', $request->email)->first();
        }

        if (!$user) {
            return app(FailedPasswordResetResponse::class, ['status' => __('passwords.user')]);
        }

        // 5. Update Password User
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // 6. Hapus Token
        PasswordResetToken::where('email', $request->email)->delete();

        // 7. Redirect ke login (Respons standar Fortify)
        return app(PasswordResetResponse::class, ['status' => __('passwords.reset')]);
    }
}
