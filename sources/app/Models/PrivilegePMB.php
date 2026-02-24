<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegePMB extends Model
{
    protected $table = 'pmb_admin_mastergroup';

    protected $primaryKey = 'KodeGroupUser';
    public $incrementing = false;
    protected $keyType = 'string';
}
