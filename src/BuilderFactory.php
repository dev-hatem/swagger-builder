<?php

namespace Creatify\SwaggerBuilder;

use Creatify\SwaggerBuilder\Enums\BuilderFormat;
use Creatify\SwaggerBuilder\Builders\YamlBuilder;
use Creatify\SwaggerBuilder\Builders\JsonBuilder;

class BuilderFactory
{
    public static function getBuilder(string $format)
    {
        return match (strtolower($format)){
            BuilderFormat::JSON->value => new JsonBuilder(),
            default => new YamlBuilder()
        };
    }
}
