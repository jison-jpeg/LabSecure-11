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
        return view('admin.user');
    }

}
