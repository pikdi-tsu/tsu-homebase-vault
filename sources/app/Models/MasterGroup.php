<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterGroup extends Model
{
    protected $table = 'mastergroup';

    // Primary Key-nya 'KodeGroupUser' (bukan ID)
    protected $primaryKey = 'KodeGroupUser';
    public $incrementing = false;
    protected $keyType = 'string';

//    protected $fillable = ['KodeGroupUser', 'NamaGroup'];
}
