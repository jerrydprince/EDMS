<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ChatEvent;
use App\Listeners\UserEventSubscriber;
use Illuminate\Auth\Events\Login;
use App\Listeners\CheckDefaultPasswordOnLogin;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\WorkflowCompleted::class => [
            \App\Listeners\ProcessPoolPostingCompletion::class,
        ],
        Login::class => [
            CheckDefaultPasswordOnLogin::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Dropbox\DropboxExtendSocialite::class.'@handle',
            \SocialiteProviders\Graph\GraphExtendSocialite::class.'@handle',
            \SocialiteProviders\Adobe\AdobeExtendSocialite::class.'@handle',
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        UserEventSubscriber::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
