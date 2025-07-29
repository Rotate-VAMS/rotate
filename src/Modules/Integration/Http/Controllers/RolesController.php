<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function jxFetchRoles(Request $request)
    {
        $roles = Role::where('tenant_id', app('currentTenant')->id)->get();
        foreach ($roles as $role) {
            $role->permissions = $role->permissions;
        }
        return response()->json([
            'hasErrors' => false,
            'data' => $roles
        ]);
    }

    public function jxCreateEditRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        if ($request->id) {
            $role = Role::find($request->id);
            $role->name = $request->name;
            if (!$role->save()) {
                $this->errorBag['hasErrors'] = true;
                $this->errorBag['message'] = 'Failed to update role';
                return response()->json($this->errorBag);
            }
        } else {
            $role = Role::create(['name' => $request->name]);
            if (!$role) {
                $this->errorBag['hasErrors'] = true;
                $this->errorBag['message'] = 'Failed to create role';
                return response()->json($this->errorBag);
            }
        }

        return response()->json(['hasErrors' => false, 'message' => 'Role created successfully']);
    }

    public function jxDeleteRole(Request $request)
    {
        $role = Role::find($request->id);
        if (!$role) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Role not found';
            return response()->json($this->errorBag);
        }

        if (!$role->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete role';
            return response()->json($this->errorBag);
        }

        return response()->json(['hasErrors' => false, 'message' => 'Role deleted successfully']);
    }

    public function jxFetchPermissions(Request $request)
    {
        $permissions = Permission::where('tenant_id', app('currentTenant')->id)->get();
        return response()->json([
            'hasErrors' => false,
            'data' => $permissions
        ]);
    }

    public function jxGiveRolePermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer',
            'permission_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $role = Role::find($request->role_id);
        $permission = Permission::where('id', $request->permission_id)->first();
        if (!$role || !$permission) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Role not found';
            return response()->json($this->errorBag);
        }

        $role->givePermissionTo($permission);

        return response()->json(['hasErrors' => false, 'message' => 'Permissions given to role']);
    }

    public function jxRevokeRolePermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer',
            'permission_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $role = Role::find($request->role_id);
        $permission = Permission::where('id', $request->permission_id)->first();
        if (!$role || !$permission) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Role not found';
            return response()->json($this->errorBag);
        }

        $role->revokePermissionTo($permission);
        return response()->json(['hasErrors' => false, 'message' => 'Permissions revoked from role']);
    }
}