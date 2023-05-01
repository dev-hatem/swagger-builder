<?php

namespace Creatify\SwaggerBuilder\Endpoints;

use Creatify\SwaggerBuilder\BaseEndpoint;
use Illuminate\Support\Str;

class Restore implements Endpoint
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
        return true;
    }

    public function description(): string
    {
        return 'Restore :model By Identifier';
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
        return sprintf('/%s/restore/{id}', Str::lower(Str::singular($this->model)));
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
