<?php

namespace Creatify\SwaggerBuilder\Commands;


use Creatify\SwaggerBuilder\BuilderFactory;
use Creatify\SwaggerBuilder\Enums\BuilderFormat;
use Illuminate\Console\Command;


class Builder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate swagger docs';


    public function handle() :void
    {
        $configurations = config('swagger-builder');

        $format = $this->choice("Which Swagger Format Do You Need", [BuilderFormat::JSON->value, BuilderFormat::YAML->value], $configurations['default_format']);

        //$configurations['default'] = $format;

        $content = str_replace(
            ['BuilderFormat::YAML->value', 'BuilderFormat::JSON->value'] ,
            $format === 'json' ? 'BuilderFormat::JSON->value' : 'BuilderFormat::YAML->value',
            file_get_contents(config_path('swagger-builder.php')));

        file_put_contents(config_path('swagger-builder.php'), $content);

        $builder = BuilderFactory::getBuilder($format);

        $builder->build($configurations);

        $this->info("swagger docs generated success");
    }

}
