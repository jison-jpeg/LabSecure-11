<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // View all users
    public function viewUsers()
    {
        $users = User::paginate(1); // Adjust the pagination limit as needed
        return view('admin.user', compact('users'));
    }

    // Fetch users data for AJAX pagination
    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $users = User::paginate(10); // Adjust the pagination limit as needed
            return view('admin.partials.users', compact('users'))->render();
        }
    }
}
