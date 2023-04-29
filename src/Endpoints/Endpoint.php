<?php

namespace Creatify\SwaggerBuilder\Endpoints;

interface Endpoint
{
    public function handle($model) :array;

    public function isSingle() :bool;

    public function description() :string;

    public function method() :string;

    public function hasSearch() :bool;

    public function hasSort() :bool;

    public function hasPagination() :bool;

    public function expectedRoute() :string;

    public function isStore() :bool;

    public function isUpdate() :bool;

    public function isDelete() :bool;
}
