<?php

namespace App\Models;

use App\Models\Passport\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'url',
        'passport_client_id',
        'isactive',
    ];

    public function accessLogs(): HasMany
    {
        return $this->hasMany(ModuleAccessLog::class);
    }

    public function passportClient()
    {
        return $this->belongsTo(Client::class, 'passport_client_id');
    }
}
