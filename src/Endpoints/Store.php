<?php

namespace Creatify\SwaggerBuilder\Endpoints;

use Creatify\SwaggerBuilder\BaseEndpoint;
use Illuminate\Support\Str;

class Store implements Endpoint
{
    use BaseEndpoint;

    private string $model;

    public function handle($model) :array
    {
        $this->model = $model;

        return [
            'security' => [['BearerAuth' => []]],
            'consumes' => 'application/json',
            'requestBody' => [
                'required' => true,
                'content'  => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => []
                        ]
                    ]
                ]
            ],
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
                422 => [
                    'description' => 'Successful operation',
                    'content'=> [
                        'application/json' => [
                            'schema'      => [
                                '$ref' => '#/components/schemas/validationError'
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
        return 'Store New :model';
    }

    public function method(): string
    {
        return 'post';
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
        return sprintf('/%s', Str::lower(Str::plural($this->model)));
    }

    public function isStore(): bool
    {
        return  true;
    }

    public function isUpdate(): bool
    {
        return false;
    }


}