<?php namespace Mitch\LaravelDoctrine\Console;

use Illuminate\Console\Command;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Symfony\Component\Console\Input\InputOption;

class SchemaRefreshCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'doctrine:schema:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the database, first droping the schema, then recreating it';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->call('doctrine:schema:drop');
        $this->call('doctrine:schema:create');
    }
} 
