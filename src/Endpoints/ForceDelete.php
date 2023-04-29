<?php

namespace Creatify\SwaggerBuilder\Endpoints;

use Creatify\SwaggerBuilder\BaseEndpoint;
use Illuminate\Support\Str;

class ForceDelete implements Endpoint
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
                                '$ref' => '#/components/schemas/modelDeleted'
                            ]
                        ]
                    ]
                ],
                404 => [
                    'description' => 'Successful operation',
                    'content'=> [
                        'application/json' => [
                            'schema'      => [
                                '$ref' => '#/components/schemas/userError'
                            ]
                        ]
                    ]
                ],
                500 => [
                    'description' => 'Successful operation',
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
        return true;
    }

    public function description(): string
    {
        return 'Force Delete :model By Identifer';
    }

    public function method(): string
    {
        return 'delete';
    }

    public function hasSearch(): bool
    {
        return false;
    }

    public function hasSort(): bool
    {
        return false;
    }

    public function hasPagination(): bool
    {
        return false;
    }

    public function expectedRoute(): string
    {
        return sprintf('/%s/force-delete/{id}', Str::lower(Str::plural($this->model)));
    }


    public function isStore(): bool
    {
        return false;
    }

    public function isUpdate(): bool
    {
        return false;
    }

    public function isDelete(): bool
    {
        return true;
    }
}
