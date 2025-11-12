<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Registered;
use App\Notifications\VerifyEmailNotification;

class EventServiceProvider extends ServiceProvider
{
    protected static bool $alreadyHandled = false;

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {

    }
}
