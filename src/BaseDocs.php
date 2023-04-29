<?php

namespace Creatify\SwaggerBuilder;

trait BaseDocs
{
    private array $configurations;

    public function build(array $configurations) :void
    {
        $this->configurations = $configurations;

        $this->buildDocsFile();
    }

    private function buildDocsFile() :void
    {
        $this->save($this->configurations['docs_file_name'] . '.' . $this->configurations['default_format']
            , $this->getDocsTemplate());
    }

    private function getDocsTemplate() :array
    {
        return [
            'openapi' => '3.0.0',
            'info'    => [
                'description' => $this->configurations['project']['description'],
                'title'       => $this->configurations['project']['name'],
                'version'     => $this->configurations['project']['version'],
            ],

            'servers' => array_map(fn($item) => ['url' => $item], $this->configurations['project']['servers']),


            'paths' => [],


            'components' => [
                'securitySchemes'    => [
                    'BearerAuth' => [
                        'in'          => 'header',
                        'type'        => 'http',
                        'scheme'      => 'bearer',
                        'name'        => 'Authorization',
                        'description' => 'Authorization header <b>Bearer {token}</b>',
                    ],
                ],
                'schemas'   => [
                    'validationError' => [
                        'description' => "Validation Error",
                        'type' => 'object',
                        'properties' => $this->configurations['response']['validation_error_schema']
                    ],
                    'exceptionError'  => [
                        'description' => "Server Error",
                        'type' => 'object',
                        'properties' => $this->configurations['response']['exception_error_schema']
                    ],
                    'userError'  => [
                        'description' => "User Error",
                        'type' => 'object',
                        'properties' => $this->configurations['response']['exception_error_schema']
                    ],
                ]
            ],
        ];
    }
}


