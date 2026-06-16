<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Admin\SupervisorCreationController;
use App\Http\Controllers\Controller;
use App\Models\RoleMapping;
use App\Models\SupervisorPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SupervisorController extends Controller
{
    public function index()
    {
        $supervisors = User::whereHas('roles', function ($query) {
            $query->where('role_name', 'Supervisor');
        })->get();

        return response()->json([
            'response code' => 200,
            'data' => $supervisors,
            'status' => true,
            'message' => 'Supervisor fetched successfully!',
        ]);
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'name'          => 'required|string',
            'mobile_no'     => 'required|numeric|digits:10',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6|confirmed'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        $supervisor = User::create([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'created_by' => auth('api')->id(),
        ]);

        $supervisorId = $supervisor->id;

        $role_mapping = RoleMapping::create([
            'user_id' => $supervisorId,
            'role_id' => 2
        ]);

        return response()->json([
            'response code' => 200,
            'data' => $supervisor,
            'status' => true,
            'message' => 'Supervisor created successfully!',
        ]);
    }

  public function update(Request $request)
{
    $validate = Validator::make($request->all(), [
        'id'            => 'required|exists:users,id',
        'name'          => 'required|string',
        'mobile_no'     => 'required|numeric|digits:10',
        'email'         => 'required|email',
        'password'      => 'required|min:6|confirmed'
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validate->errors()
        ], 422);
    }

    $supervisor = User::findOrFail($request->id);

    $supervisor->update([
        'name'       => $request->name,
        'mobile_no'  => $request->mobile_no,
        'email'      => $request->email,
        'password'   => Hash::make($request->password),
        'updated_by' => auth('api')->id(),
    ]);

    return response()->json([
        'response_code' => 200,
        'data' => $supervisor,
        'status' => true,
        'message' => 'Supervisor updated successfully!',
    ]);
}


    public function delete($id)
    {
        $supervisor = User::findOrFail($id);
        $supervisor->delete();

        return response()->json([
            'response code' => 200,
            'data' => $supervisor,
            'status' => true,
            'message' => 'Supervisor deleted successfully!',
        ]);
    }

    public function getPermissions($id)
    {
        $supervisor = User::findOrFail($id);

        $modules = SupervisorCreationController::permissionModules();

        $saved = SupervisorPermission::where('supervisor_id', $id)
            ->get()
            ->keyBy(fn($p) => $p->module . '.' . $p->action);

        $permissions = [];
        foreach ($modules as $moduleKey => $module) {
            foreach ($module['actions'] as $actionKey => $action) {
                $key = $moduleKey . '.' . $actionKey;
                $permissions[$moduleKey][$actionKey] = isset($saved[$key]) && $saved[$key]->is_allowed ? 1 : 0;
            }
        }

        return response()->json([
            'response_code' => 200,
            'status'        => true,
            'message'       => 'Permissions fetched successfully!',
            'supervisor_id' => (int) $id,
            'supervisor'    => $supervisor->name,
            'permissions'   => $permissions,
        ]);
    }
}
