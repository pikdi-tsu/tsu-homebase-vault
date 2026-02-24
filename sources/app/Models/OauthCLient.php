<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Client as PassportClient;

class OauthCLient extends PassportClient
{
    protected $fillable = [
//        'id', // Tambahkan 'id' juga karena kita mengisinya manual
        'name',
        'secret',
        'provider',
        'redirect_uris', // Gunakan redirect_uri, bukan redirect
        'grant_types',
        'revoked',
    ];
}
