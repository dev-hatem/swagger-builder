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
    
    'save_generated_files' => false,

    'response' => [
        // 200 additional info with data
        'schema' => [
            /*
            'identifierCode' => 'integer',
            'status'         => 'boolean',
            'message'        => 'string',
            'error'          => 'string',
            */
        ],

        //50x errors response standard format
        'exception_error_schema' => [
            /*
            'identifierCode' => ['type' => 'integer', 'example' => 9999999999],
            'message'        => ['type' => 'string'],
            'error'          => ['type' => 'string'],
            */
        ],

        //422 errors response standard format
        'validation_error_schema' => [
            /*
            'identifierCode' => ['type' => 'integer', 'example' => 8888888888],
            'message'        => ['type' => 'string'],
            'error'          => ['type' => 'string'],
            */
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
