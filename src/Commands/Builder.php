<?php

namespace Creatify\SwaggerBuilder\Commands;

use Creatify\SwaggerBuilder\BuilderFactory;
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

        $format = $this->choice("Which Swagger Format Do You Need", ['json', 'yaml'], 'yaml');

        $builder = BuilderFactory::getBuilder($format);

        $builder->build($configurations);

        $this->info("swagger docs generated success");
    }

}
