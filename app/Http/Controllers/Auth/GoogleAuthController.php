<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();
        $user = User::where('email', $googleUser->getEmail())->first();

        // Check if the user exists in the database
        if (!$user) {
            // Return an error response if the user doesn't exist
            return redirect()->route('login')->withErrors([
                'username' => 'Account is not registered. Please contact the administrator.',
            ]);
        }

        if (!$user->isActive()) {
            Auth::logout(); // Log the user out
            return redirect()->route('login')->withErrors([
                'username' => 'Your account is not active. You may contact the administrator to settle your account.',
            ]);
        }

        if (!$user->profile_picture) {
            // Store the Google profile picture URL
            $user->profile_picture = $googleUser->getAvatar(); // Assuming getAvatar() returns the profile picture URL
            $user->save();
        }

        // If the user exists, log them in
        Auth::login($user, true);

        return redirect()->intended('/dashboard'); // Redirect to your intended route
    }
}
