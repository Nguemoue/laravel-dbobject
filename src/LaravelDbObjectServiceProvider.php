<?php

namespace Nguemoue\LaravelDbObject;

use Nguemoue\LaravelDbObject\Commands\DboMakeCommand;
use Nguemoue\LaravelDbObject\Commands\DboMigrateCommand;
use Nguemoue\LaravelDbObject\Commands\DboRedoCommand;
use Nguemoue\LaravelDbObject\Commands\DboRefreshCommand;
use Nguemoue\LaravelDbObject\Commands\DboRollbackCommand;
use Nguemoue\LaravelDbObject\Commands\DboStatusCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDbObjectServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-db-objects')
            ->hasConfigFile('db-objects')
            ->hasMigrations('create_dbo_migrations_table')
            ->hasCommands(
                DboMakeCommand::class,
                DboMigrateCommand::class,
                DboRollbackCommand::class,
                DboStatusCommand::class,
                DboRedoCommand::class,
                DboRefreshCommand::class,
            );
    }

    /*public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/dbobjects.php' => config_path('dbobjects.php'),
            __DIR__.'/../database/dbo' => base_path('database/dbo'),
        ], 'dbobjects');

    }*/
    /*public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dbobjects.php', 'dbobjects');
    }*/

}
