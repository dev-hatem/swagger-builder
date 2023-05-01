<?php

namespace Creatify\SwaggerBuilder\Endpoints;

use Creatify\SwaggerBuilder\BaseEndpoint;
use Illuminate\Support\Str;

class Page implements Endpoint
{
    use BaseEndpoint;

    private string $model;

    public function handle($model) :array
    {
        $this->model = $model;

        return [
            'security' => [['BearerAuth' => []]],
            'responses' => [
                200 => [
                    'description' => 'Successful operation',
                    'content'=> [
                        'application/json' => [
                            'schema'      => [
                                '$ref' => '#/components/schemas/'.$this->schemaModelName()
                            ]
                        ]
                    ]
                ],

                404 => [
                    'description' => 'Not Found',
                    'content'=> [
                        'application/json' => [
                            'schema'      => [
                                '$ref' => '#/components/schemas/userError'
                            ]
                        ]
                    ]
                ],
                500 => [
                    'description' => 'Server Error',
                    'content'=> [
                        'application/json' => [
                            'schema'      => [
                                '$ref' => '#/components/schemas/exceptionError'
                            ]
                        ]
                    ]
                ]
            ]
        ];

    }

    public function isSingle() :bool
    {
        return false;
    }

    public function description(): string
    {
        return 'Listing All :model With Pagination';
    }

    public function method(): string
    {
        return 'get';
    }

    public function hasSearch(): bool
    {
        return true;
    }

    public function hasSort(): bool
    {
        return true;
    }

    public function hasPagination(): bool
    {
        return true;
    }

    public function expectedRoute(): string
    {
        return sprintf('/%s/paging', Str::lower(Str::plural($this->model)));
    }

    public function isStore(): bool
    {
        return false;
    }

    public function isUpdate(): bool
    {
        return false;
    }


}
