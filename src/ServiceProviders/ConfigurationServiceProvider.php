<?php

namespace Mitch\LaravelDoctrine\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use Mitch\LaravelDoctrine\Configuration\DriverMapper;
use Mitch\LaravelDoctrine\Configuration\SqlMapper;
use Mitch\LaravelDoctrine\Configuration\SqliteMapper;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the configuration requirements for the package.
     */
    public function register()
    {
        $this->app->bind(DriverMapper::class, function () {
            $mapper = new DriverMapper;
            $mapper->registerMapper(new SqlMapper);
            $mapper->registerMapper(new SqliteMapper);
            return $mapper;
        });
    }
}
