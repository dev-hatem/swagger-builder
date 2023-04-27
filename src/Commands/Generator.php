<?php

namespace Creatify\SwaggerBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class Generator extends Command
{
    private $configurations = [];
    private $dir = null;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:build {model : the model name represent dir name} {endpoints?* : the name of endpoints method separated by comma}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate swagger yaml file from array and save it to file';

    /**
     * @return int
     * @throws \Brick\VarExporter\ExportException
     */
    public function handle()
    {

//        $base = '<?php return %s;';
//        file_put_contents('z.php', sprintf($base, VarExporter::export(Yaml::parseFile('x.yaml'),VarExporter::TRAILING_COMMA_IN_ARRAY)));

        $endpoints = $this->argument('endpoints');
        $dirName   = $this->argument('model');


        $path = base_path('swagger-generator' . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . 'config.php');

        if (!File::exists($path)){
            $this->error(sprintf('undefined model %s', $dirName));
            return Command::FAILURE;
        }

        $this->configurations = File::getRequire($path);
        $this->dir = $dirName;

        $endpoints = empty($endpoints) || in_array($endpoints[0], ['*', 'all']) ? ['index', 'show', 'paging', 'store', 'update', 'delete'] : $endpoints;

        $this->newLine();
        $this->info('generating yaml files for this endpoints');
        $this->newLine();

        $this->output->table($endpoints, []);
        $this->newLine();

        $this->output->progressStart(count($endpoints));
        $this->newLine();
        $this->newLine();

        foreach ($endpoints as $endpoint){
            if (method_exists($this, $endpoint)){
                $this->info("starting generate yaml file for endpoint $endpoint");
                call_user_func([$this, $endpoint]);
                $this->newLine();
            } else{
                $this->newLine();
                $this->warn(sprintf('undefined endpoint %s', $endpoint));
            }
            $this->newLine();
            $this->output->progressAdvance();
            $this->newLine();
        }

        $this->output->progressFinish();

        return Command::SUCCESS;
    }

    private function paging()
    {

        $method = 'get';

        $template = [
            'get' => [
                'security' => [
                    [
                        'Bearer' => [],
                    ],
                ],
                'responses' => [
                    200 => [
                        'description' => 'Successful operation',
                        'schema'      => $this->successResponse(false, true)
                    ],
                    '422' => $this->validationError(),
                    '40x' => $this->userError(),
                    '50x' => $this->serverError()
                ]
            ],
        ];

        $tag = $this->ask('Enter Tag Name: ', $this->dir);
        $template[$method]['tags'] = [$tag];

        $summary = $this->ask('Enter Endpoint Summary: ', 'Listing All ' . Str::plural($this->dir) . ' with pagination');
        $template[$method]['summary'] = $summary;

        $description = $this->ask('Enter Endpoint Description: ', 'Listing All ' . Str::plural($this->dir) . ' with pagination');
        $template[$method]['description'] = $description;

        $hasSecurity = $this->confirm('Endpoint Has Security', true);
        if (!$hasSecurity){
            unset($template[$method]['security']);
        }

        $hasParameter = $this->confirm('Endpoint Has Parameter', true);
        if ($hasParameter){
            $hasSort = $this->confirm('Endpoint Has Sort', true);
            if ($hasSort){
                $columns = $this->ask('Enter Endpoint Columns separated by comma(,): ');
                $sortOptions = [
                    'in'        => 'query',
                    'name'      => 'sort',
                    'required'  => false,
                    'nullable'  => true,
                    'schema'    => NULL,
                    'type'      => 'array',
                    'items'     => ['type' => 'string'],
                    'description' =>"* asc ?sort=column1,column2\n* desc ?sort=-column1,-column2\n* minus operator `-column` before each column mean the desc order"
                ];

                $sortOptions['items']['enum'] = explode(',', $columns);
                $sortOptions['items']['example'] = explode(',', $columns)[0];
                $template[$method]['parameters'][] = $sortOptions;

            }

            $hasPaginationLength = $this->confirm('Endpoint Has pagination length', true);
            if ($hasPaginationLength){
                $lengths = $this->ask('Enter Endpoint lengths separated by comma(,): ');
                $paginationOptions = [
                    'in'        => 'query',
                    'name'      => 'length',
                    'required'  => false,
                    'nullable'  => true,
                    'schema'    => NULL,
                    'type'      => 'integer',
                    'description' =>"* determine the number of items per page ?length=10"
                ];
                $paginationOptions['enum'] = explode(',', $lengths);
                $template[$method]['parameters'][] = $paginationOptions;
            }

            $hasSearch = $this->confirm('Endpoint Has Search', true);
            if ($hasSearch){
                $template[$method]['parameters'][] = [
                    'in'        => 'query',
                    'name'      => 'search',
                    'required'  => false,
                    'nullable'  => true,
                    'schema'    => ['type' => 'string'],
                    'description' =>"* any value"
                ];
            }
        }

        $this->saveFile($template, __FUNCTION__);
    }

    private function index()
    {
        $method = 'get';

        $template = [
            'get' => [
                'security' => [
                    [
                        'Bearer' => [],
                    ],
                ],
                'responses' => [
                    200 => [
                        'description' => 'Successful operation',
                        'schema'      => $this->successResponse(false, false)
                    ],
                    '422' => $this->validationError(),
                    '40x' => $this->userError(),
                    '50x' => $this->serverError()
                ]
            ],
        ];

        $tag = $this->ask('Enter Tag Name: ', $this->dir);
        $template[$method]['tags'] = [$tag];

        $summary = $this->ask('Enter Endpoint Summary: ', 'Listing All ' . Str::plural($this->dir));
        $template[$method]['summary'] = $summary;

        $description = $this->ask('Enter Endpoint Description: ', 'Listing All ' . Str::plural($this->dir));
        $template[$method]['description'] = $description;

        $hasSecurity = $this->confirm('Endpoint Has Security', true);
        if (!$hasSecurity){
            unset($template[$method]['security']);
        }

        $hasParameter = $this->confirm('Endpoint Has Parameter', true);
        if ($hasParameter){
            $hasSort = $this->confirm('Endpoint Has Sort', true);
            if ($hasSort){
                $columns = $this->ask('Enter Endpoint Columns separated by comma(,): ');
                $sortOptions = [
                    'in'        => 'query',
                    'name'      => 'sort',
                    'required'  => false,
                    'nullable'  => true,
                    'schema'    => NULL,
                    'type'      => 'array',
                    'items'     => ['type' => 'string'],
                    'description' =>"* asc ?sort=column1,column2\n* desc ?sort=-column1,-column2\n* minus operator `-column` before each column mean the desc order"
                ];

                $sortOptions['items']['enum'] = explode(',', $columns);
                $sortOptions['items']['example'] = explode(',', $columns)[0];
                $template[$method]['parameters'][] = $sortOptions;

            }

            $hasSearch = $this->confirm('Endpoint Has Search', true);
            if ($hasSearch){
                $template[$method]['parameters'][] = [
                    'in'        => 'query',
                    'name'      => 'search',
                    'required'  => false,
                    'nullable'  => true,
                    'schema'    => ['type' => 'string'],
                    'description' =>"* any value"
                ];
            }
        }

        $this->saveFile($template, __FUNCTION__);

    }

    private function show()
    {
        $method = 'get';

        $template = [
            'get' => [
                'parameters' => [
                    [
                        'in' => 'path',
                        'name' => 'id',
                        'required' => true,
                        'schema' => [
                            'type' => 'integer',
                            'minimum' => 1,
                        ],
                        'description' => 'item id',
                    ],
                ],
                'security' => [
                    [
                        'Bearer' => [],
                    ],
                ],
                'responses' => [
                    200 => [
                        'description' => 'Successful operation',
                        'schema'      => $this->successResponse(true, false)
                    ],
                    '422' => $this->validationError(),
                    '40x' => $this->userError(),
                    '50x' => $this->serverError()
                ]
            ],
        ];

        $tag = $this->ask('Enter Tag Name: ', $this->dir);
        $template[$method]['tags'] = [$tag];

        $summary = $this->ask('Enter Endpoint Summary: ', 'show ' . $this->dir . ' by id');
        $template[$method]['summary'] = $summary;

        $description = $this->ask('Enter Endpoint Description: ', 'show ' . $this->dir . ' by id');
        $template[$method]['description'] = $description;

        $hasSecurity = $this->confirm('Endpoint Has Security', true);
        if (!$hasSecurity){
            unset($template[$method]['security']);
        }

        $this->saveFile($template, __FUNCTION__);
    }

    private function store()
    {
        $method = 'post';

        $template = [
            $method => [
                'consumes' => [
                    'application/json',
                ],
                'parameters' => [
                    [
                        'name' => 'properties',
                        'in' => 'body',
                        'description' => 'Object containing key-value pairs of properties for the object',
                        'schema' => [
                            'type' => 'object',
                            'properties' => $this->configurations['store'],
                        ]
                    ],
                ],
                'security' => [
                    [
                        'Bearer' => [],
                    ],
                ],
                'responses' => [
                    200 => [
                        'description' => 'Successful operation',
                        'schema'      => $this->successResponse(true, false)
                    ],
                    '40x' => $this->userError(),
                    '50x' => $this->serverError()
                ]
            ],
        ];

        $tag = $this->ask('Enter Tag Name: ', $this->dir);
        $template[$method]['tags'] = [$tag];

        $summary = $this->ask('Enter Endpoint Summary: ', 'Add New ' . $this->dir);
        $template[$method]['summary'] = $summary;

        $description = $this->ask('Enter Endpoint Description: ', 'Add New ' . $this->dir);
        $template[$method]['description'] = $description;

        $hasSecurity = $this->confirm('Endpoint Has Security', true);
        if (!$hasSecurity){
            unset($template[$method]['security']);
        }

        $this->saveFile($template, __FUNCTION__);
    }

    public function delete()
    {
        $method = 'delete';

        $template = [
            $method => [
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'description' => 'item id',
                    ],
                ],
                'security' => [
                    [
                        'Bearer' => [],
                    ],
                ],
                'responses' => [
                    200 => [
                        'description' => 'Successful operation',
                        'schema'      => $this->prepareSuccessResponseWithoutPagination()
                    ],
                    '40x' => $this->userError(),
                    '50x' => $this->serverError()
                ]
            ],
        ];

        $tag = $this->ask('Enter Tag Name: ', $this->dir);
        $template[$method]['tags'] = [$tag];

        $summary = $this->ask('Enter Endpoint Summary: ', 'delete ' . $this->dir . ' by id');
        $template[$method]['summary'] = $summary;

        $description = $this->ask('Enter Endpoint Description: ', 'delete ' . $this->dir . ' by id');
        $template[$method]['description'] = $description;

        $hasSecurity = $this->confirm('Endpoint Has Security', true);
        if (!$hasSecurity){
            unset($template[$method]['security']);
        }

        $this->saveFile($template, __FUNCTION__);
    }

    public function update()
    {
        $method = 'put';

        $template = [
            $method => [
                'consumes' => [
                    'application/json',
                ],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'description' => 'item id',
                    ],
                    [
                        'name' => 'properties',
                        'in' => 'body',
                        'description' => 'Object containing key-value pairs of properties for the object',
                        'schema' => [
                            'type' => 'object',
                            'properties' => $this->configurations['update'],
                        ]
                    ],
                ],
                'security' => [
                    [
                        'Bearer' => [],
                    ],
                ],
                'responses' => [
                    200 => [
                        'description' => 'Successful operation',
                        'schema'      => $this->successResponse(true, false)
                    ],
                    '40x' => $this->userError(),
                    '50x' => $this->serverError()
                ]
            ],
        ];

        $tag = $this->ask('Enter Tag Name: ', $this->dir);
        $template[$method]['tags'] = [$tag];

        $summary = $this->ask('Enter Endpoint Summary: ', 'Update ' . $this->dir . ' by id');
        $template[$method]['summary'] = $summary;

        $description = $this->ask('Enter Endpoint Description: ', 'Update ' . $this->dir . ' by id');
        $template[$method]['description'] = $description;

        $hasSecurity = $this->confirm('Endpoint Has Security', true);
        if (!$hasSecurity){
            unset($template[$method]['security']);
        }

        $this->saveFile($template, __FUNCTION__);
    }

    ############### CORE ####################

    private function getEndPointObjectSchema()
    {
        return $this->configurations['oneObject'];
    }

    private function paginationMeta()
    {
        return [
            'type' => 'object',
            'properties' => [
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
        ];
    }

    private function paginationLinks()
    {
        return [
            'type' => 'object',
            'properties' => [
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
            ]
        ];
    }

    private function serverError()
    {
        return [
            'description' => 'Some thing wrong',
            'schema' => [
                'type' => 'object',
                'properties' => [
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
            ],
        ];
    }

    private function userError()
    {
        return [
            'description' => 'Some thing wrong',
            'schema' => [
                'type' => 'object',
                'properties' => [
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
            ],
        ];
    }

    private function validationError()
    {
        return [
            'description' => 'Object not found',
            'schema' => [
                'type' => 'object',
                'properties' => [
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
            ],
        ];
    }

    private function prepareSuccessResponse()
    {
        return [
            'type' => 'object',
            'properties' => [
                'data' => [],
                'links' => $this->paginationLinks(),
                'meta' => $this->paginationMeta(),
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
            ]
        ];

    }

    private function prepareSuccessResponseWithoutPagination()
    {
       $response = $this->prepareSuccessResponse();

        unset($response['properties']['links']);
        unset($response['properties']['meta']);

        return $response;
    }

    private function successResponse($isSingle = true, $hasPagination = false)
    {
        if (!$isSingle)
            return $this->multiSuccessResponse($hasPagination);

        $response = $this->prepareSuccessResponseWithoutPagination();

        $response['properties']['data'] = [
            'type' => 'object',
            'properties' => $this->getEndPointObjectSchema(),
        ];

        return $response;
    }

    private function multiSuccessResponse($hasPagination = false)
    {
        $response = $hasPagination ? $this->prepareSuccessResponse() : $this->prepareSuccessResponseWithoutPagination();

        $response['properties']['data'] = [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => $this->getEndPointObjectSchema(),
            ],
        ];

        return $response;
    }

    private function saveFile($template, $name)
    {
        $path = base_path('swagger-generator' . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $name . '.yaml');

        file_put_contents($path, Yaml::dump($template, 20, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

        $this->info("swagger for endpoint $name generated success");

    }
}
