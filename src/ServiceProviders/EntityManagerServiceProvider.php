<?php

namespace Mitch\LaravelDoctrine\ServiceProviders;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Tools\Setup;
use Event;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\ServiceProvider;
use Mitch\LaravelDoctrine\CacheManager;
use Mitch\LaravelDoctrine\EventListeners\SoftDeletableListener;
use Mitch\LaravelDoctrine\EventListeners\TablePrefixListener;
use Mitch\LaravelDoctrine\Filters\TrashedFilter;

class EntityManagerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Stores the active connection configuration provided by Laravel.
     *
     * @var array
     */
    private $connectionConfiguration;

    /**
     * Registers the entity manager and associated/dependant classes.
     */
    public function register()
    {
        $this->registerEntityManager();
        $this->registerClassMetadataFactory();
    }

    /**
     * Once ready, extend the auth manager with our required doctrine auth implementation.
     */
    public function boot()
    {
        $this->extendAuthManager();
    }

    /**
     * Registers and configures the entity manager to be used with and by Doctrine.
     */
    private function registerEntityManager()
    {
        $this->app->singleton(EntityManager::class, function ($app) {
            $config = $app['config']['doctrine::doctrine'];

            $metadata = $this->buildMetaDataConfiguration($config);

            $eventManager = new EventManager;
            $eventManager->addEventListener(Events::loadClassMetadata, new TablePrefixListener($this->connection->prefix));
            $eventManager->addEventListener(Events::onFlush, new SoftDeletableListener);
            $entityManager = EntityManager::create($this->mapLaravelToDoctrineConfig($app['config']), $metadata, $eventManager);
            $entityManager->getFilters()->enable('trashed');

            return $entityManager;
        });

        $this->app->singleton(EntityManagerInterface::class, EntityManager::class);
    }

    /**
     * Simply returns the meta data factory utilised by the entity manager.
     */
    private function registerClassMetadataFactory()
    {
        $this->app->singleton(ClassMetadataFactory::class, function ($app) {
            return $app[EntityManager::class]->getMetadataFactory();
        });
    }

    /**
     * Extend Laravel's base auth manager class with our own implementation.
     */
    private function extendAuthManager()
    {
        $this->app[AuthManager::class]->extend('doctrine', function ($app) {
            return new DoctrineUserProvider(
                $app['Illuminate\Hashing\HasherInterface'],
                $app[EntityManager::class],
                $app['config']['auth.model']
            );
        });
    }

    /**
     * Map Laravel's to Doctrine's database configuration requirements.
     *
     * @param $config
     * @throws \Exception
     * @return array
     */
    private function mapLaravelToDoctrineConfig($config)
    {
        $default = $config['database.default'];
        $this->connectionConfiguration = $config["database.connections.{$default}"];

        return App::make(DriverMapper::class)->map($this->connectionConfiguration);
    }

    /**
     * Constructs the configuration object required for the entity manager.
     *
     * @param array $config
     * @return \Doctrine\ORM\Configuration
     * @throws \Doctrine\ORM\ORMException
     */
    private function buildMetaDataConfiguration(array $config)
    {
        $metadata = Setup::createAnnotationMetadataConfiguration(
            $config['metadata'],
            $this->app['config']['app.debug'],
            $config['proxy']['directory'],
            $this->app[CacheManager::class]->getCache($config['cache_provider']),
            $config['simple_annotations']
        );

        $metadata->addFilter('trashed', TrashedFilter::class);
        $metadata->setAutoGenerateProxyClasses($config['proxy']['auto_generate']);
        $metadata->setDefaultRepositoryClassName($config['repository']);
        $metadata->setSQLLogger($config['logger']);

        if (isset($config['proxy']['namespace'])) {
            $metadata->setProxyNamespace($config['proxy']['namespace']);
        }

        // Fire off an event so that others can hook into the configuration object
        Event::fire('doctrine.metadata.configuration', [$metadata]);

        return $metadata;
    }
}
