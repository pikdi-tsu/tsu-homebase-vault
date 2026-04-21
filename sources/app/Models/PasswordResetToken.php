<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    // Arahkan ke tabel bawaan Laravel
    protected $table = 'password_reset_tokens';

    // Primary key-nya bukan id, tapi email
    protected $primaryKey = 'email';

    // Matikan auto-increment karena primary key-nya string (email)
    public $incrementing = false;
    protected $keyType = 'string';

    // Matikan timestamps otomatis karena tabel ini cuma punya created_at
    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];
}
