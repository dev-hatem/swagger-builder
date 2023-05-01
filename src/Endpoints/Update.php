<?php

namespace Creatify\SwaggerBuilder\Endpoints;

use Creatify\SwaggerBuilder\BaseEndpoint;
use Illuminate\Support\Str;

class Update implements Endpoint
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
                    'description' => 'Validation Error',
                    'content'=> [
                        'application/json' => [
                            'schema'      => [
                                '$ref' => '#/components/schemas/validationError'
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
        return true;
    }

    public function description(): string
    {
        return 'Update :model';
    }

    public function method(): string
    {
        return 'put';
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
        return sprintf('/%s/{id}', Str::lower(Str::singular($this->model)));
    }

    public function isStore(): bool
    {
        return false;
    }

    public function isUpdate(): bool
    {
        return true;
    }


}
