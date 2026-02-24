<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengelompokkan permission berdasarkan guard-nya agar lebih rapi
        return Permission::all()->groupBy('guard_name');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        return $permission;
    }
}
