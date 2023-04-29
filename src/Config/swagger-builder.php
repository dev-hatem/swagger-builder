<?php

return [

    'docs_file_name' => 'doc',

    'default_format' => Creatify\SwaggerBuilder\Enums\BuilderFormat::YAML->value,

    'project' => [
        'description' => 'Laravel Swagger Builder',
        'name'        => 'SwaggerBuilder',
        'version'     => '1.0.0',
        'servers'     => [
            'https://jsonplaceholder.typicode.com',
        ],
    ],

    'save_dir'  => public_path('swagger'),

    'endpoints_dir' => public_path('swagger/endpoints'),

    'has_pagination_links' => true,

    'has_pagination_mata' => true,

    'response' => [
        'schema' => [
            'identifierCode' => 'integer',
            'status'         => 'boolean',
            'message'        => 'string',
            'error'          => 'string',
        ],

        'exception_error_schema' => [
            'identifierCode' => ['type' => 'integer', 'example' => 9999999999],
            'message'        => ['type' => 'string'],
            'error'          => ['type' => 'string'],
        ],

        'validation_error_schema' => [
            'identifierCode' => ['type' => 'integer', 'example' => 8888888888],
            'message'        => ['type' => 'string'],
            'error'          => ['type' => 'string'],
        ],
    ],
    'endpoints' => [
        'paging'        => \Creatify\SwaggerBuilder\Endpoints\Page::class,
        'index'         => \Creatify\SwaggerBuilder\Endpoints\Index::class,
        'show'          => \Creatify\SwaggerBuilder\Endpoints\Show::class,
        'store'         => \Creatify\SwaggerBuilder\Endpoints\Store::class,
        'update'        => \Creatify\SwaggerBuilder\Endpoints\Update::class,
        'delete'        => \Creatify\SwaggerBuilder\Endpoints\Delete::class,
        'restore'       => \Creatify\SwaggerBuilder\Endpoints\Restore::class,
        'forceDelete'   => \Creatify\SwaggerBuilder\Endpoints\ForceDelete::class,
    ]
];
