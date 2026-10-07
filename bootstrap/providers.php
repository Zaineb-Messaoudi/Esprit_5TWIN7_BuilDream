<?php

// Listeners in app/Listeners are auto-discovered by Laravel 12 (each handle() type-hints
// its event), so App\Providers\EventServiceProvider must not be registered here as well,
// otherwise every listener would be attached twice.
return [
    App\Providers\AppServiceProvider::class,
];
