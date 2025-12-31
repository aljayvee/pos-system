<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin-notifications', function ($user) {
    // Modify this if you want to restrict to specific admin roles
    return in_array($user->role, ['admin', 'manager', 'cashier']);
});
