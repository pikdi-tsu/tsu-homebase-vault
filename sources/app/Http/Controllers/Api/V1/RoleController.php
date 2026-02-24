<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Role::with('permissions:id,name')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'required|string',
            'permissions' => 'sometimes|array'
        ]);

        $role = Role::create($data);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions:id,name'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return $role->load('permissions:id,name');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Melindungi role penting agar tidak bisa diubah namanya
        if (in_array($role->name, ['admin', 'super admin'])) {
            return response()->json(['message' => 'Role ini tidak dapat diubah.'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'sometimes|array'
        ]);

        $role->update(['name' => $data['name']]);

        if ($request->has('permissions')) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions:id,name');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::query()->findOrFail($id);

        // Melindungi role penting agar tidak bisa dihapus
        if (in_array($role->name, ['admin', 'super admin'])) {
            return response()->json(['message' => 'Role ini tidak dapat dihapus.'], 403);
        }

        $role->delete();

        return response()->json(null, 204);
    }

    public function syncList()
    {
        $roles = Role::query()
            ->select('name', 'is_identity')
            ->get()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'is_identity' => (bool) $role->is_identity
                ];
            });

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Role list for sync retrieved successfully.'
            ],
            'data' => $roles
        ]);
    }
}
