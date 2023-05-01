<?php

namespace Creatify\SwaggerBuilder\Commands;

use Creatify\SwaggerBuilder\Enums\BuilderFormat;
use Creatify\SwaggerBuilder\SwaggerBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
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
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate swagger yaml file from array and save it to file';

    /**
     * @return int
     */
    public function handle():int
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

    /**
     * @param $endpoint
     * @param $model
     * @param $schema
     * @return int|void
     */
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


        if ($this->isYaml())
            File::put($path, Yaml::dump($data, 20, 1, Yaml::DUMP_OBJECT));
        else
            File::put($path, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));


        if (!isset($this->documentation['paths'][$route_path]))
            $this->documentation['paths'][$route_path] = [];

        $this->documentation['paths'][$route_path][$endpoint->method()] = $template;

        if ($this->isYaml())
            File::put($this->getDocsPath(), Yaml::dump($this->documentation, 20, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
        else
            File::put($this->getDocsPath(), json_encode($this->documentation, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $this->info("swagger docs generated success");
    }

    /**
     * @return bool
     */
    private function getDocsContent() :bool
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

    /**
     * @return string
     */
    private function getDocsPath() :string
    {
        return $this->configurations['save_dir'] . DIRECTORY_SEPARATOR . $this->configurations['docs_file_name'] . '.' . $this->configurations['default_format'];
    }

    private function isYaml()
    {
        return $this->configurations['default_format'] === BuilderFormat::YAML->value;
    }


    /**
     * @return array
     */
    private function paginationMeta() :array
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

    /**
     * @return array
     */
    private function paginationLinks() :array
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

    /**
     * @param $model
     * @return void
     */
    private function handleModel($model) :void
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

    /**
     * @param $endpoint
     * @param $baseSchema
     * @param bool $isDelete
     * @return array
     */
    private function buildResponseSchema($endpoint, $baseSchema, bool $isDelete = false) :array
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

    /**
     * @param $data
     * @return mixed
     */
    private function prepareObject($data):array
    {
        foreach ($data as $key => $value){
            if (($key === 'type' && is_string($value) && in_array($value, $this->dataTypes())) || in_array($key, ['example', 'enum']))
                continue;

            if (is_string($value))
                $data[$key] = ['type' => $value];
            else
                $data[$key] = $this->prepareObject($value);
        }
        return $data;
    }

    private function dataTypes() :array
    {
        return  [
            'object',
            'integer',
            'long',
            'float',
            'double',
            'string',
            'byte',
            'binary',
            'boolean',
            'date',
            'dateTime',
            'password',
        ];
    }
}
