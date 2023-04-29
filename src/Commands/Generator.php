<?php

namespace Creatify\SwaggerBuilder\Commands;

use Brick\VarExporter\VarExporter;
use Creatify\SwaggerBuilder\BuilderFactory;
use Creatify\SwaggerBuilder\Enums\BuilderFormat;
use Creatify\SwaggerBuilder\SwaggerBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class Generator extends Command
{

    private $configurations = [];
    private $documentation  = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:gen';

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
        $this->configurations = config('swagger-builder');

        if (!$this->getDocsContent()){
            $this->error('Please Build Docs First By swagger:build');
            return Command::FAILURE;
        }

        $path = $this->configurations['endpoints_dir'];

        $models = [];

        foreach (glob($path . DIRECTORY_SEPARATOR . '*.php') as $model){
            $models[ucwords(pathinfo($model, PATHINFO_FILENAME))] = $model;
        }

        $selectedModel = $this->choice('Which Docs Do You Want To Generate', array_keys(array_merge(['*' => 'All'], $models)), '*');

        if ($selectedModel !== '*'){
            $models = [$selectedModel => $models[$selectedModel]];
        }

        foreach ($models as $model){
            $this->handleModel($model);
        }

        return Command::SUCCESS;
    }

    private function handleEndpoint($endpoint, $model, $schema)
    {
        $endpoint = new $endpoint;
        $swaggerBuilder = new SwaggerBuilder($endpoint);

        $template = $swaggerBuilder->handle($model);

        $route_path = $this->ask('Enter The Route', $endpoint->expectedRoute());

        $route_path = sprintf('/%s', trim($route_path, '/'));

        preg_match_all('/\{[a-z0-9]+\}/i', $route_path,$matches);

        $params = [];

        if (count($matches[0]) > 0){
         foreach ($matches[0] as $param){
             $params[] = str_replace(['{', '}'], ['', ''], $param);
         }
        }

        $template['summary'] = str_replace(':model', ucwords(Str::plural($model)), $endpoint->description());
        $template['description'] = $template['summary'];

        $tag = $this->ask("Enter $model Endpoint Tag Name: ", Str::plural($model));
        $template['tags'] = [$tag];

        $hasSecurity = $this->confirm('Is Endpoint Secured', true);
        if (!$hasSecurity){
            unset($template['security']);
        }

        if ($endpoint->hasSearch()){
            $hasSearch = $this->confirm('Endpoint Has Search', true);
            if ($hasSearch){
                $template['parameters'][] = [
                    'in'        => 'query',
                    'name'      => 'search',
                    'required'  => false,
                    'schema'    => ['type' => 'string'],
                    'description' =>"* any value"
                ];
            }
        }

        if ($endpoint->hasSort()){
            $hasSort = $this->confirm('Endpoint Has Sort', true);
            if ($hasSort){
                $columns = $this->ask("Enter $model Endpoint Columns separated by comma(,): ", 'created_at');
                $sortOptions = [
                    'in'        => 'query',
                    'name'      => 'sort',
                    'schema'    => [
                        'type'      => 'array',
                        'items'     => ['type' => 'string'],
                    ],
                    'required'  => false,
                    'description' =>"* asc ?sort=column1,column2\n* desc ?sort=-column1,-column2\n* minus operator `-column` before each column mean the desc order"
                ];

                $sortOptions['schema']['items']['enum'] = explode(',', $columns);
                $sortOptions['schema']['items']['example'] = explode(',', $columns)[0];
                $template['parameters'][] = $sortOptions;
            }
        }

        if ($endpoint->hasPagination()){
            $lengths = $this->ask("Enter $model Endpoint lengths separated by comma(,): ");
            $paginationOptions = [
                'in'        => 'query',
                'name'      => 'length',
                'required'  => false,
                'schema'    => [
                    'type'      => 'integer',
                ],
                'description' =>"* determine the number of items per page ?length=10"
            ];
            $paginationOptions['schema']['enum'] = explode(',', $lengths);
            $template['parameters'][] = $paginationOptions;
        }

        foreach ($params as $param){
            $paramOptions = [
                'in' => 'path',
                'name' => $param,
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
                'description' => "$param value",
            ];
            $template['parameters'][] = $paramOptions;
        }

        if ($endpoint->isStore() || $endpoint->isUpdate()){
            $template['requestBody']['content']['application/json']['schema']['properties'] = $this->prepareObject($schema[$endpoint->isStore() ? 'store' : 'update']);
        }

        $schema['schema'] = $this->prepareObject($schema['schema']);

        if (isset($this->documentation['components']['schema'][$model])){
            $this->warn("Model $model Already Exist");
            $accepted = $this->confirm("Do You Want To Override The Existing Schema", true);
            if (!$accepted){
                return Command::FAILURE;
            }
        }

        $this->documentation['components']['schemas'][$endpoint->isDelete() ? 'modelDeleted' : $endpoint->schemaModelName()] = $this->buildResponseSchema($endpoint, $schema['schema'], $endpoint->isDelete());
        $file_name =  $model . '.' . $this->configurations['default_format'];
        $path = $this->configurations['save_dir'] . DIRECTORY_SEPARATOR . $file_name;

        if (File::exists($path)){
            $data = Yaml::parseFile($path);
            if (isset($data['paths'][$route_path][$endpoint->method()])){
                $accepted = $this->confirm("Do You Want To Override The Existing route", true);
                if (!$accepted){
                    $this->info("Operation Ended Successfully Without Any Changes");
                    return Command::SUCCESS;
                }
            }
        }else{
            $data['paths'][$route_path] = [];
        }


        $data['paths'][$route_path][$endpoint->method()] = $template;

        File::put($path, Yaml::dump($data, 20, 1, Yaml::DUMP_OBJECT));



//        if (!in_array($file_name.'#paths', array_column($this->documentation['paths']['allOf'], '$ref')))
//            $this->documentation['paths']['allOf'][] = ['$ref' => $file_name . '#paths'];

        if (!isset($this->documentation['paths'][$route_path]))
            $this->documentation['paths'][$route_path] = [];

        $this->documentation['paths'][$route_path][$endpoint->method()] = $template;

        File::put($this->getDocsPath(), Yaml::dump($this->documentation, 20, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

        $this->info("swagger docs generated success");
    }

    private function getDocsContent()
    {
        $path = $this->getDocsPath();

        if (!file_exists($path))
            return false;

        if (pathinfo($path, PATHINFO_EXTENSION) === BuilderFormat::JSON)
            $this->documentation = json_decode(file_get_contents($path), true);
        else
            $this->documentation = Yaml::parseFile($path);

        return true;
    }

    private function getDocsPath()
    {
        return $this->configurations['save_dir'] . DIRECTORY_SEPARATOR . $this->configurations['docs_file_name'] . '.' . $this->configurations['default_format'];
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

    private function handleModel($model)
    {
        $endpoints = $this->configurations['endpoints'];

        $configurations = File::getRequire($model);

        $selectedModelEndpoints = $this->choice('Which Endpoint(s) Do You Want To Generate', array_merge(['*'],  array_keys($endpoints)), '*');

        if ($selectedModelEndpoints === '*')
            $selectedModelEndpoints = array_keys($endpoints);
        else
            $selectedModelEndpoints = [$selectedModelEndpoints];

        foreach ($selectedModelEndpoints as $endpoint){
            $this->handleEndpoint($endpoints[$endpoint], pathinfo($model, PATHINFO_FILENAME), $configurations);
        }
    }

    private function buildResponseSchema($endpoint, $baseSchema, $isDelete = false)
    {

        $generalResponse = [];
        foreach ($this->configurations['response']['schema'] as $key => $type){
            $generalResponse[$key] = ['type' => $type];
        }

        if ($isDelete){
            return [
                'type'  => 'object',
                'properties' => ['data' => [], ...$generalResponse]
            ];
        }


        if ($endpoint->isSingle()) {
            $properties =  array_merge([
                'data' => [
                    'type' => 'object',
                    'properties' => $baseSchema
                ]
            ], $generalResponse);


            return  ['type' => 'object', 'properties' => $properties];
        }

        $properties =  [
            'type'  => 'object',
            'properties' => [
                'data' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => $baseSchema
                    ]
                ]
            ]
        ];

        if ($endpoint->hasPagination()){
          $properties['properties']['meta'] = $this->paginationMeta();
          $properties['properties']['links'] = $this->paginationLinks();
        }

        $properties['properties'] =  array_merge($properties['properties'], $generalResponse);


        return $properties;
    }

    private function prepareObject($data)
    {
        foreach ($data as $key => $value){
            if (is_string($value))
                $data[$key] = ['type' => $value];
        }
        return $data;
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




        $path = config('swagger-builder.save_dir') . DIRECTORY_SEPARATOR .  config('swagger-builder.docs_file_name');

        $files = [];

        foreach (glob($path . '.*') as $file){
            $files[] = pathinfo($file, PATHINFO_EXTENSION);
        }

        if (empty($files))
            return $this->error("Please Build Docs First by command swagger:build");

        $file  = $files[0];

        if (count($files) > 0)
            $file = $this->choice('Which Docs Do You Want To Generate', $files, $files[0]);

        $path.=".$file";
        $base = '<?php return %s;';

        $docs = Yaml::parseFile($path);

        $docs['paths'][] = [];




        dd(sprintf($base, VarExporter::export(Yaml::parseFile($path),VarExporter::TRAILING_COMMA_IN_ARRAY)));
        file_put_contents('z.php', sprintf($base, VarExporter::export(Yaml::parseFile($path),VarExporter::TRAILING_COMMA_IN_ARRAY)));


        // get docs path here

        // append data in path key




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


    }
}
