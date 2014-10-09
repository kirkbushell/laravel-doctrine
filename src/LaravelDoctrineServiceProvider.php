<?php namespace Mitch\LaravelDoctrine;

use Illuminate\Support\ServiceProvider;

/**
 * Class LaravelDoctrineServiceProvider
 *
 * The main package service provider - provides all the other service providers which manage their own dependencies,
 * configuration tools and bindings that are necessary to be used with Laravel.
 *
 * @package Mitch\LaravelDoctrine
 */
class LaravelDoctrineServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = false;

    private $serviceProviders = [
        'ConfigurationServiceProvider',
        'CacheManagerServiceProvider',
        'CommandsServiceProvider',
        'EntityManagerServiceProvider',
        'ValidationServiceProvider',
    ];

    /**
     * Boot the service provider, registering the package and defining the namespace.
     */
    public function boot()
    {
        $this->package('mitchellvanw/laravel-doctrine', 'doctrine', __DIR__ . '/..');
    }

    public function register()
    {
        foreach ($this->serviceProviders as $provider) {
            $this->app->register('Mitch\\LaravelDoctrine\\ServiceProviders\\'.$provider);
        }
    }
}
