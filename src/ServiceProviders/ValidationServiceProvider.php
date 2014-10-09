<?php

namespace Mitch\LaravelDoctrine\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use Mitch\LaravelDoctrine\Validation\DoctrinePresenceVerifier;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the validation service provider, but more importantly - when Laravel's
     * ValidationServiceProvider is registered, then we want to set our own presence verifier,
     * which is required for validation rules such as unique.
     */
    public function register()
    {
        $closure = function() {
            $this->app['validation.presence'] = new DoctrinePresenceVerifier;
        };

        $closure->bindTo($this);

        $this->app['events']->listen('Illuminate\Validation\ValidationServiceProvider', $closure);
    }
}
