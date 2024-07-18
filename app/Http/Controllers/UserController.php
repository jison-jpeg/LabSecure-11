<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // View all users
    public function viewUsers()
    {
        $users = User::paginate(10); // Adjust the pagination limit as needed
        return view('admin.user', compact('users'));
    }

    // Bulk delete users
    public function bulkDelete(Request $request)
    {
        $userIds = $request->input('user_ids');
        if ($userIds) {
            User::whereIn('id', $userIds)->delete();
            return redirect()->route('users')->with('success', 'Selected users deleted successfully.');
        }
        return redirect()->route('users')->with('error', 'No users selected for deletion.');
    }

}
