<?php

namespace App\Http\Controllers\Auth;

use App\Models\PasswordResetToken;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use App\Mail\SendResetPasswordToken;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Illuminate\Contracts\Support\Responsable;

class CustomPasswordResetLinkController extends PasswordResetLinkController
{
    public function store(Request $request): Responsable
    {
        // 1. Validasi
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        // 2. Cari user (LOGIKA KUSTOM-MU)
        $user = UserMahasiswa::query()->where('email', $email)->first();
        if (!$user) {
            $user = UserDosenTendik::query()->where('email', $email)->first();
        }

        // 3. Keamanan: Selalu kirim respons sukses
        if (!$user) {
            return app(SuccessfulPasswordResetLinkRequestResponse::class, ['status' => __('passwords.sent')]);
        }

        // 4. Buat Token
        PasswordResetToken::where('email', $email)->delete();
        $token = Str::random(64);

        PasswordResetToken::create([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // 5. Kirim Email
        try {
            Mail::to($user)->send(new SendResetPasswordToken($token, $email, $user->name));
        } catch (\Exception $e) {
            Log::error('Gagal kirim email reset password: ' . $e->getMessage());
        }

        // 6. Kembalikan respons 'web' standar Fortify
        return app(SuccessfulPasswordResetLinkRequestResponse::class, ['status' => __('passwords.sent')]);
    }
}
