<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    private function getRedirectUri()
    {
        return route('sso.callback');
    }

    public function redirect()
    {
        $baseUrl = config('app.url');

        $redirectUri = rtrim($baseUrl, '/') . '/login/sso/callback';

        $query = http_build_query([
            'client_id' => config('passport.authorization_grant_client.id'),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => '',
            'state' => Str::random(40),
        ]);

        return redirect('http://tsu-homebase-vault.test/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $state = session()->pull('sso_state');

        // Validasi keamanan state
        if ($state !== '' && $state === $request->state) {

            // Tukar "Code" jadi "Token" (Server-to-Server, user gak tau proses ini)
            $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('passport.authorization_grant_client.id'),
                'client_secret' => config('passport.authorization_grant_client.secret'),
                'redirect_uri' => $this->getRedirectUri(),
                'code' => $request->code,
            ]);

            if ($response->failed()) {
                return redirect('/login')->with('error', 'Gagal validasi ke server pusat TSU.');
            }

            $tokens = $response->json();
            $accessToken = $tokens['access_token'];
            $refreshToken = $tokens['refresh_token'] ?? null;

            $userResponse = Http::withToken($accessToken)->get(config('services.tsu.base_url') . '/api/user');

            if ($userResponse->failed()) {
                return redirect('/login')->with('error', 'Gagal mengambil data user.');
            }

            $tsuUser = $userResponse->json();

        } else {
            return redirect('/login')->with('error', 'Login invalid!');
        }
    }
}
