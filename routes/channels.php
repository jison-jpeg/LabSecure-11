<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('attendance-channel', function ($user) {
//     return $user;
// });

// Public channel for attendance
Broadcast::channel('attendance-channel', function ($user) {
    return $user;
});

// Public channel for laboratory
Broadcast::channel('laboratory-channel', function ($user) {
    return $user;
});