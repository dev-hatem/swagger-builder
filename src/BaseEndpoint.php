<?php

namespace Creatify\SwaggerBuilder;

trait BaseEndpoint
{
    public function schemaModelName()
    {
        return $this->isSingle() ? $this->model : ($this->hasPagination() ? "Pagination-$this->model" : "Multi-$this->model");
    }

    public function isDelete() :bool
    {
        return false;
    }
}


