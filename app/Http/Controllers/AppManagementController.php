<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isNull;

class AppManagementController extends Controller
{

    public function GetAppUsersList()
    {
        if (Gate::allows('Get-All-users')) {
            $sort = request()->header("sort");
            $query = User::query();
            if ($sort == "newest") {
                $query->orderBy('created_at', 'desc');
            }
            $data[] = $query->get(['id', 'name', 'prof_img_url'])->paginate(20);
            return response()->json(["data" => $data], 200);
        }
        return response()->json(["ُmessage" => __('messages.You are not allowed to see this')], 401);
    }

    public function blockUser(Request $request)
    {
        //
    }

    public function unBlockUser(Request $request)
    {
        //
    }

    public function blockedUsersList($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
