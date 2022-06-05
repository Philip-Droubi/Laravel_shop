<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        if (Gate::allows('Super-Admin-Protection')) {
            $roles = Role::all();
            foreach ($roles as $role) {
                $data[] = [
                    "id" => $role->id,
                    "name" => $role->name,
                    "description" => $role->description,
                    "created_at" => $role->created_at->format("Y-m-d H-i-s"),
                    "updated_at" => $role->updated_at->format("Y-m-d H-i-s")
                ];
            }
            return response()->json(["data" => $data], 200);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function store(Request $request)
    {
        if (Gate::allows('Super-Admin-Protection')) {
            $input = $request->only('name', 'description', 'ability');
            $request->validate([
                'name' => ['required', 'min:2', 'max:255', 'unique:roles,name'],
                'description' => ['required', 'string', 'max:1000'],
                'ability' => ['required', 'array']
            ]);
            $data = Role::create($input);
            return response()->json($data, 200);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function show(Request $request, $id)
    {
        if (Gate::allows('Super-Admin-Protection')) {
            $role = Role::find($id);
            if ($role) {
                $data[] = [
                    "id" => $role->id,
                    "name" => $role->name,
                    "description" => $role->description,
                    "created_at" => $role->created_at->format("Y-m-d H-i-s"),
                    "updated_at" => $role->updated_at->format("Y-m-d H-i-s")
                ];
                return response()->json(["data" => $data], 200);
            }
            return response()->json(["message" => __("messages.There seems to be a problem")], 404);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function showUserWithRole($id)
    {
        if (Gate::allows('Super-Admin-Protection')) {
            $role = Role::find($id);
            if ($role) {
                $users = $role->users()->orderByDesc('created_at')->paginate(30);
                $data = [];
                foreach ($users as $user) {
                    $data[] = [
                        "id" => $user->id,
                        "name" => $user->name,
                        "prof_img_url" => $user->prof_img_url,
                    ];
                }
                return response()->json(["data" => $data], 200);
            }
            return response()->json(["message" => __("messages.There seems to be a problem")], 404);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function update(Request $request, $id)
    {
        if (Gate::allows('Super-Admin-Protection')) {
            $role = Role::find($id);
            if ($role) {
                $input = $request->only('name', 'description', 'ability');
                $request->validate([
                    'name' => ['min:2', 'max:255',  Rule::unique('roles', 'name')->ignore($role), 'nullable'],
                    'description' => ['string', 'max:1000', 'nullable'],
                    'ability' => ['array', 'nullable']
                ]);
                $role->update($input);
                return response()->json(["message" => "success"], 200);
            }
            return response()->json(["message" => __("messages.There seems to be a problem")], 404);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function destroy($id)
    {
        if (Gate::allows('Super-Admin-Protection')) {
            $role = Role::find($id);
            if ($role) {
                if ($id == 1 || $id == 2)
                    return response()->json(["message" => __("messages.You can not delete this role")], 400);
                $users = User::query()->where('role_id', $id)->get();
                foreach ($users as $user) {
                    $user->update([
                        'role_id' => 1
                    ]);
                }
                $role->delete();
                return response()->json(["message" => "success"], 200);
            }
            return response()->json(["message" => __("messages.There seems to be a problem")], 404);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function giverole(Request $request)
    {
        if (Gate::allows('Super-Admin-Protection')) {
            $request->validate([
                'user_id' => ['required', 'numeric'],
                'role_id' => ['required', 'numeric'],
            ]);
            $role = Role::find($request->role_id);
            if ($role) {
                $user = User::find($request->user_id);
                if ($user) {
                    $user->update([
                        'role_id' => $request->role_id
                    ]);
                    return response()->json(["message" => "success"], 200);
                }
                return response()->json(["message" => __("messages.This user is not exist")], 404);
            }
            return response()->json(["message" => __("messages.this role is not exist")], 404);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }
}
