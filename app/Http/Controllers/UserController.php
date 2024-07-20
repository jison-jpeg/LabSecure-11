<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // View all users
    public function viewUsers(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('middle_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->input('role') != '') {
            $query->where('role_id', $request->input('role'));
        }

        $users = $query->paginate(10);
        $roles = Role::all();

        return view('admin.user', compact('users', 'roles'));
    }

}
