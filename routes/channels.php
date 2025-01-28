<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('antrian', function () {
    return true;
});

Broadcast::channel('rekam_medis', function() {
    return true;
});