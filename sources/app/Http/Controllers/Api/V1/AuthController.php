<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\SendResetPasswordToken;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// 1. Import HTTP Client

class AuthController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function loginDosenTendik(Request $request): \Illuminate\Http\JsonResponse
    {
        // Panggil proxy dengan provider untuk dosen/tendik
        return $this->proxyLogin($request, 'dosen_tendik');
    }

    /**
     * @throws ConnectionException
     */
    public function loginMahasiswa(Request $request): \Illuminate\Http\JsonResponse
    {
        // Panggil proxy dengan provider untuk mahasiswa
        return $this->proxyLogin($request, 'mahasiswa');
    }

    /**
     * @throws ConnectionException
     */
    private function proxyLogin(Request $request, string $provider): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Ambil User berdasarkan provider
        $user = null;
        if ($provider === 'mahasiswa') {
            $userModel = UserMahasiswa::class;
        } else {
            $userModel = UserDosenTendik::class;
        }
        $user = $userModel::query()->where('email', $request->email)->first();

        // Cek email user
        if (!$user) {
            throw ValidationException::withMessages([
                'message' => ['Email yang Anda masukkan salah.'],
            ]);
        }

        // Cek Password user
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['Password yang Anda masukkan salah.'],
            ]);
        }

        // Cek Status User
        if (!$user->isactive) {
            throw ValidationException::withMessages([
                'message' => ['Akun Anda telah dinonaktifkan. Silakan hubungi admin.'],
            ]);
        }

        // Buat request internal ke endpoint /oauth/token milik Passport
        $response = Http::asForm()->withHeaders([
            'Accept' => 'application/json',
        ])->withoutVerifying()->post(config('app.url').'/oauth/token', [
            'grant_type' => 'password',
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '',
            'provider' => $provider,
        ]);

        // Jika gagal, kirimkan error yang sesuai
        if ($response->failed()) {
            $errorData = $response->json();
            $statusCode = $response->status();

            // Cek jenis errornya
            $errorType = $errorData['error'] ?? 'unknown';

            // Server salah konfigurasi (client_id/secret salah)
            if ($statusCode === 401 && $errorType === 'invalid_client') {
                // Ini error fatal. Kita log untuk developer, tapi user dapat pesan umum.
                Log::error('Kesalahan Konfigurasi Passport: Client ID atau Secret salah.', $errorData);

                return response()->json([
                    'message' => 'Terjadi kesalahan pada server. Harap hubungi administrator.'
                ], 500); // Kirim 500 Internal Server Error
            }

            // Jika ada error lain
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak diketahui saat login.',
                'debug_error' => $errorData // Kirim error asli untuk debug
            ], $statusCode);
        }

        // Jika berhasil, teruskan respons dari Passport (berisi token)
        return response()->json($response->json(), $response->status());
    }

    /**
     * @throws ConnectionException
     */
    public function refreshToken(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validasi refresh_token
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        // Buat request internal ke Passport
        $response = Http::withoutVerifying()
        ->withHeaders(['Accept' => 'application/json'])
        ->asForm()
        ->post(config('app.url').'/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'scope' => '',
        ]);

        // Handle jika Gagal (misal refresh_token expired atau dicabut)
        if ($response->failed()) {
            $errorData = $response->json();
            $statusCode = $response->status();

            // Cek jenis errornya
            $errorType = $errorData['error'] ?? 'unknown';

            // Server salah konfigurasi (client_id/secret salah)
            if ($statusCode === 401 && $errorType === 'invalid_client') {
                // Log untuk Developer.
                Log::error('Kesalahan Konfigurasi Passport: Client ID atau Secret salah.', $errorData);

                return response()->json([
                    'message' => 'Terjadi kesalahan pada server. Harap hubungi administrator.'
                ], 500); // Kirim 500 Internal Server Error
            }

            // Refresh token salah
            if ($statusCode === 400 && $errorType === 'invalid_grant') {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.'
                ], 401); // 401 akan memicu front-end untuk auto-logout
            }

            // Jika ada error lain
            return response()->json([
                'message' => 'Terjadi kesalahan yang tidak diketahui saat login.',
                'debug_error' => $errorData // Kirim error asli untuk debug
            ], $statusCode);
        }

        // Kirim token baru ke client
        return response()->json($response->json(), $response->status());
    }

    public function getMe(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(Auth::user());
    }

    /**
     * Endpoint: POST /api/forgot-password
     * Mengirim link reset password ke email user.
     */
    public function sendResetLink(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validasi email
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        // Cari user
        $user = UserMahasiswa::query()->where('email', $email)->first();
        if (!$user) {
            $user = UserDosenTendik::query()->where('email', $email)->first();
        }

        // Selalu kirim respons sukses untuk keamanan.
        if (!$user) {
            return response()->json(['message' => 'Jika email Anda terdaftar, link reset akan dikirim.']);
        }

        // Buat Token dan Hapus token lama jika ada
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Buat token acak
        $token = Str::random(64);

        // Simpan token ke database
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token, // Token disimpan plain-text (standar Laravel)
            'created_at' => Carbon::now()
        ]);

        // Kirim Email
        try {
            Mail::to($user)->send(new SendResetPasswordToken($token, $email));
        } catch (\Exception $e) {
            Log::error('Gagal kirim email reset password: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Jika email Anda terdaftar, link reset akan dikirim.']);
    }

    /**
     * Endpoint: POST /api/reset-password
     * Memvalidasi token & mereset password user.
     */
    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validasi input
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan cek 'password_confirmation'
        ]);

        // Cek Token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        // Handle Token Tidak Valid
        if (!$resetRecord) {
            return response()->json(['message' => 'Token reset password tidak valid.'], 422);
        }

        // Handle Token Kedaluwarsa (Standar 60 menit)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete(); // Hapus token kedaluwarsa
            return response()->json(['message' => 'Token reset password telah kedaluwarsa.'], 422);
        }

        // Cari user
        $user = UserMahasiswa::query()->where('email', $request->email)->first();
        if (!$user) {
            $user = UserDosenTendik::query()->where('email', $request->email)->first();
        }

        if (!$user) {
            // Jika user tidak ditemukan
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Ganti Password User
        $user->password = Hash::make($request->password);
        $user->save();

        // 7. Hapus Token setelah dipakai
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password Anda telah berhasil direset. Silakan login.']);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        // Ambil token yang sedang digunakan untuk request ini, lalu cabut (revoke)
        $user = Auth::user();

        // Cabut access token yang sedang dia pakai
        $user->token()?->revoke();

        // Cabut semua refresh token-nya
         $user->tokens->each(function ($token, $key) {
             $token->revoke();
         });

        return response()->json(['message' => 'Logout berhasil'], 200);
    }
}
