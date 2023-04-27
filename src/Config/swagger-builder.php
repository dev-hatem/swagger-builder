<?php

return [

    'project' => [
        'description' => 'Laravel Swagger Builder',
        'name'        => 'SwaggerBuilder',
        'version'     => 1.0
    ],

    'save_dir' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'swagger',

    'response' => [
        'general' => [
            'identifierCode' => [
                'type' => 'integer',
                'format' => 'int32',
            ],
            'status' => [
                'type' => 'boolean',
            ],
            'message' => [
                'type' => 'string',
            ],
            'error' => [
                'type' => 'object',
            ],
        ],

        'exception_error' => [
            'identifierCode' => [
                'type' => 'integer',
                'format' => 'int32',
                'example' => 9999999999,
            ],
            'message' => [
                'type' => 'string',
            ],
            'error' => [
                'type' => 'object',
            ]
        ],

        'validation_error' => [
            'identifierCode' => [
                'type' => 'integer',
                'format' => 'int32',
                'example' => 8888888888,
            ],
            'message' => [
                'type' => 'string',
                'example' => 'Validation Error'
            ],
            'error' => [
                'type' => 'object',
            ]
        ],

        'pagination' => [
            'links' => [
                'first' => [
                    'type' => 'string',
                ],
                'last' => [
                    'type' => 'string',
                ],
                'prev' => [
                    'type' => 'string',
                ],
                'next' => [
                    'type' => 'string',
                ],
            ],
            'meta' => [
                'current_page' => [
                    'type' => 'integer',
                ],
                'from' => [
                    'type' => 'integer',
                ],
                'last_page' => [
                    'type' => 'integer',
                ],
                'links' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'url' => [
                                'type' => 'string',
                            ],
                            'label' => [
                                'type' => 'string',
                            ],
                            'active' => [
                                'type' => 'boolean',
                            ],
                        ],
                    ],
                ],
            ]
        ]

    ]
];
