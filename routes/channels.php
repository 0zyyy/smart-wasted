<?php

use Illuminate\Support\Facades\Broadcast;

// Public channels — any authenticated user can subscribe
Broadcast::channel('measurements', fn ($user) => true);
Broadcast::channel('alerts', fn ($user) => true);
