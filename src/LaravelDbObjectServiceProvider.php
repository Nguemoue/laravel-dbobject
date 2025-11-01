<?php

namespace Nguemoue\LaravelDbObject;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nguemoue\LaravelDbObject\Commands\LaravelDbObjectCommand;
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
                \Nguemoue\LaravelDbObject\Commands\DboMakeCommand::class,
                \Nguemoue\LaravelDbObject\Commands\DboMigrateCommand::class,
                \Nguemoue\LaravelDbObject\Commands\DboRollbackCommand::class,
                \Nguemoue\LaravelDbObject\Commands\DboStatusCommand::class,
                \Nguemoue\LaravelDbObject\Commands\DboRedoCommand::class,
                \Nguemoue\LaravelDbObject\Commands\DboRefreshCommand::class,
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
