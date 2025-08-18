<?php

namespace App\Providers;

use App\Providers\PlainTextUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::provider('plaintext', function ($app, array $config) {
            return new PlainTextUserProvider($app['hash'], $config['model']);
        });
    }
}