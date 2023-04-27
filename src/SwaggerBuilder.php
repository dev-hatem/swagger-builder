<?php

namespace Creatify\SwaggerBuilder;

class SwaggerBuilder implements SwaggerBuilderInterface
{
    public function __construct(private readonly string $format)
    {

    }

    public function generatePagination()
    {}

    public function generateIndex()
    {}

    public function generateShow()
    {}

    public function generateStore()
    {}

    public function generateUpdate()
    {}

    public function generateDelete()
    {}

    public function generateRestore()
    {}

    public function generateForceDelete()
    {}

}
