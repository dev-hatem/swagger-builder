<?php

namespace Creatify\SwaggerBuilder\Providers;

use Creatify\SwaggerBuilder\Commands\Builder;
use Creatify\SwaggerBuilder\Commands\Generator;
use Illuminate\Support\ServiceProvider;

class SwaggerBuilderServiceProvider extends ServiceProvider
{
    private string $tag = 'swagger-builder';

    /**
     * Register any application services.
     */
    public function register(): void
    {
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
        $this->loadRoutesFrom(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'swagger.php');

        $configDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Config';

        $this->mergeConfigFrom($configDir . DIRECTORY_SEPARATOR . 'swagger-builder.php', 'swagger');

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'assets' => public_path('vendor' . DIRECTORY_SEPARATOR . 'swagger'),
        ], $this->tag);

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'documentation.blade.php' => resource_path('views' . DIRECTORY_SEPARATOR . 'documentation.blade.php'),
        ], $this->tag);

        $this->publishes([
            $configDir . DIRECTORY_SEPARATOR . 'swagger-builder.php' => config_path('swagger-builder.php'),
        ], $this->tag);
    }
}
