<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleAccessLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    // Relasi (Opsional, buat nanti kalau mau bikin UI)
    public function targetUser() {
        return $this->morphTo();
    }

    public function admin() {
        return $this->belongsTo(UserDosenTendik::class, 'admin_id');
    }

    public function module() {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function prunable()
    {
        return static::where('accessed_at', '<=', now()->subMonth());
    }
}
