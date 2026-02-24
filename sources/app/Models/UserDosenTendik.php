<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasAvatar;

class UserDosenTendik extends Authenticatable implements OAuthenticatable, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasApiTokens;
    use HasUuids;

    protected $table = 'users_dosen_tendik';

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'nidn',
        'name',
        'email',
        'password',
        'profile_photo_path',
        'unit',
        'role_access',
        'privilege_pmb',
        'q1',
        'a1',
        'q2',
        'a2',
        'forgot_password_send_email',
        'created_by',
        'updated_by',
        'isactive',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'a1', // Sembunyikan jawaban keamanan secara default
        'a2', // Sembunyikan jawaban keamanan secara default
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'user_type',
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    public function getUserTypeAttribute(): string
    {
        return 'dosen_tendik';
    }

    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=FFFFFF&background=2d394a';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    public function backupData(): BelongsTo
    {
        return $this->belongsTo(BackupUsersDosenTendik::class, 'nik', 'nip');
    }

    public function pertanyaanKeamananSatu(): BelongsTo
    {
        return $this->belongsTo(PertanyaanKeamanan::class, 'q1');
    }

    public function pertanyaanKeamananDua(): BelongsTo
    {
        return $this->belongsTo(PertanyaanKeamanan::class, 'q2');
    }

    public function MasterGroup()
    {
        return $this->belongsTo(MasterGroup::class, 'role_access', 'KodeGroupUser');
    }

    public function MasterGroupPMB()
    {
        return $this->belongsTo(PrivilegePMB::class, 'privilege_pmb', 'KodeGroupUser');
    }
}
