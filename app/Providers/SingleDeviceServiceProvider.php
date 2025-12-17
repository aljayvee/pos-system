<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\UpdateActiveSession;
use Illuminate\Routing\Router;
use App\Http\Middleware\ForceSingleDevice;

class SingleDeviceServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
        // 1. Register the Event Listener
        Event::listen(
            Login::class,
            [UpdateActiveSession::class, 'handle']
        );

        // 2. Register the Middleware Globally (or mostly global)
        // This pushes the middleware to the 'web' group automatically
        $router->pushMiddlewareToGroup('web', ForceSingleDevice::class);
    }
}