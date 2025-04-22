<?php

namespace App\Providers;

use App\Notifications\Channels\TwilioChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('twilio', function ($app) {
                return new TwilioChannel();
            });
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 