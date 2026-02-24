<?php

namespace Database\Seeders;

use App\Models\OauthCLient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OauthDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OauthCLient::query()->create([
            'name' => 'Client Credentials Access Test',
            'secret' => Str::random(40),
            'provider' => 'users',
            'grant_types' => ['client_credentials'],
            'revoked' => false,
        ]);
        OauthCLient::query()->create([
            'name' => 'Password Grant Access Test',
            'secret' => Str::random(40),
            'provider' => 'users',
            'grant_types' => ['password', 'refresh_token'],
            'revoked' => false,
        ]);
        OauthCLient::query()->create([
            'name' => 'Authorization Code Access Test',
            'secret' => Str::random(40),
            'provider' => 'users',
            'redirect_uris' => ['https://tsu-project-template.test/login/sso/callback'],
            'grant_types' => ['authorization_code'],
            'revoked' => false,
        ]);
        OauthCLient::query()->create([
            'name' => 'Personal Access Token Test',
            'secret' => Str::random(40),
            'provider' => 'users',
            'grant_types' => ['personal_access'],
            'revoked' => false,
        ]);

        // Oauth TSU Template
        OauthCLient::query()->create([
            'name' => 'Client Credentials Access TSU Template',
            'secret' => Str::random(40),
            'provider' => 'users',
            'grant_types' => ['client_credentials'],
            'revoked' => false,
        ]);

//        OauthCLient::query()->create([
//            'name' => 'Password Grant Access TSU Template',
//            'secret' => Str::random(40),
//            'provider' => 'users',
//            'grant_types' => ['password', 'refresh_token'],
//            'revoked' => false,
//        ]);

        OauthCLient::query()->create([
            'name' => 'Authorization Code Access TSU Template',
            'secret' => Str::random(40),
            'provider' => 'users',
            'redirect_uris' => ['https://tsu-project-template.test/login/sso/callback'],
            'grant_types' => ['authorization_code'],
            'revoked' => false,
        ]);

//        OauthCLient::query()->create([
//            'name' => 'Personal Access Token TSU Template',
//            'secret' => Str::random(40),
//            'provider' => 'users',
//            'grant_types' => ['personal_access'],
//            'revoked' => false,
//        ]);
    }
}
