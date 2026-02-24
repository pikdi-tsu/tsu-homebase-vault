<?php

namespace App\Models\Passport;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Client as BaseClient;

class Client extends BaseClient
{
    public function skipsAuthorization(Authenticatable $user, array $scopes = []): bool
    {
        if ($this->first_party) {
            return true;
        }

        return parent::skipsAuthorization($user, $scopes);
    }
}
