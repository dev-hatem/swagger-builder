<?php

namespace Creatify\SwaggerBuilder;

use Creatify\SwaggerBuilder\Endpoints\Endpoint;

class SwaggerBuilder implements SwaggerBuilderInterface
{
    public function __construct(private readonly Endpoint $endpoint){}

    public function handle($model)
    {
        return $this->endpoint->handle($model);
    }
}
