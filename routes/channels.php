<?php

use Illuminate\Support\Facades\Broadcast;

// Public channels — any authenticated user can subscribe
Broadcast::channel('measurements', fn ($user) => true);
Broadcast::channel('alerts', fn ($user) => true);

// Private user channel — for Filament real-time notifications
Broadcast::channel('App.Models.User.{id}', fn ($user, $id) => (int) $user->id === (int) $id);
