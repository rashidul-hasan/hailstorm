<?php

namespace Rashidul\Hailstorm;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Rashidul\Hailstorm\Form\Builder;
use Rashidul\Hailstorm\Hook\Events;
use Rashidul\Hailstorm\JavaScript\LaravelViewBinder;
use Rashidul\Hailstorm\JavaScript\PHPToJavaScriptTransformer;
use Rashidul\Hailstorm\Route\ResourceRegister;
use Rashidul\Hailstorm\Table\DataTableBuilder;
use Rashidul\Hailstorm\Table\DetailsTableBuilder;

class HailstormServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // load views
        $this->loadViewsFrom(__DIR__.'/../views', 'hailstorm');

        // publish views
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/vendor/hailstorm'),
        ], 'hailstorm');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/hailstorm'),
        ], 'hailstorm-public');

        // publish configs
        $this->publishes([
            __DIR__ . '/../configs' => config_path('hailstorm'),
        ], 'hailstorm');

        // publish stub files for the generator
        $this->publishes([
            __DIR__ . '/Generator/stubs/' => base_path('resources/hailstorm/'),
        ], 'hailstorm');

        // for js vars
        // https://github.com/laracasts/PHP-Vars-To-Js-Transformer
        AliasLoader::getInstance()->alias(
            'JavaScript',
            'Rashidul\Hailstorm\Facades\JavaScript'
        );

        // register new resource route methods
        $registrar = new ResourceRegister($this->app['router']);

        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () use ($registrar) {
            return $registrar;
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        // register form builder
        $this->app->bind('formbuilder', function ($app) {
            return new Builder();
        });

        // register datatable builder
        $this->app->bind('datatable-builder', function () {
            return new DataTableBuilder();
        });

        // register details table builder
        $this->app->bind('detailstable', function () {
            return new DetailsTableBuilder();
        });
        //$this->app->alias(DetailsTableBuilder::class, 'detailstable');

        // register hook service providers
        $this->app->singleton('eventy', function ($app) {
            return new Events();
        });

        // register js vars stuffs
        // https://github.com/laracasts/PHP-Vars-To-Js-Transformer
        $this->app->singleton('JavaScript', function ($app) {
            $view = 'hailstorm::scripts.php-to-js';
            $namespace = 'hailstorm';

            $binder = new LaravelViewBinder($app['events'], $view);

            return new PHPToJavaScriptTransformer($binder, $namespace);
        });

        // load configs
        $this->mergeConfigFrom(
            __DIR__ . '/../configs/form.php', 'raindrops.form'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../configs/table.php', 'raindrops.table'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../configs/crud.php', 'raindrops.crud'
        );

        // register console commands
        $this->commands(
            'Rashidul\Hailstorm\Generator\Command\ScaffoldCommand',
            'Rashidul\Hailstorm\Generator\Command\MakeControllerCommand',
            'Rashidul\Hailstorm\Generator\Command\MakeModelCommand',
            'Rashidul\Hailstorm\Generator\Command\MakeMigrationCommand'
        );

    }
}
