<?php

namespace Creatify\SwaggerBuilder;

use BuilderFormat;
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
