<?php

namespace Creatify\SwaggerBuilder\Providers;

use Creatify\SwaggerBuilder\Commands\Builder;
use Creatify\SwaggerBuilder\Commands\Generator;
use Illuminate\Support\ServiceProvider;

class SwaggerBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $configDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Config';

        $this->mergeConfigFrom($configDir . DIRECTORY_SEPARATOR . 'swagger-builder.php', 'swagger');

        $this->commands([
            Builder::class,
            Generator::class
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' => public_path('vendor' . DIRECTORY_SEPARATOR . 'swagger'),
        ]);

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'docs.blade.php' => resource_path('views'),
        ]);

        $this->publishes([
            $configDir . DIRECTORY_SEPARATOR . 'swagger-builder.php' => config_path('swagger-builder.php'),
        ], 'config');
    }
}
