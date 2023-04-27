<?php

namespace Creatify\SwaggerBuilder;

use Symfony\Component\Yaml\Yaml;

class YamlBuilder
{
    private array $configurations;
    public function build(array $configurations) :void
    {
        $this->configurations = $configurations;

        $this->buildDomainFile();

        $this->buildDocsFile();
    }


    private function buildDomainFile() :void
    {
        file_put_contents($this->configurations['save_dir'] . DIRECTORY_SEPARATOR . 'domain.yaml', Yaml::dump($this->getDomainTemplate(), 20, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }


    private function buildDocsFile() :void
    {
        file_put_contents($this->configurations['save_dir'] . DIRECTORY_SEPARATOR . 'docs.yaml', Yaml::dump($this, 20, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }

    private function getDomainTemplate() :array
    {
        return [
            'host' => 'www.example.com',
            'path' => '/api'
        ];
    }

    private function getDocsTemplate() :array
    {
        return [
            'swagger' => '3.0',
            'info'    => [
                'description' => $this->configurations['project']['description'],
                'title'       => $this->configurations['project']['title'],
                'version'     => $this->configurations['project']['version'],
            ],
            'host' => '$ref: domain.yaml#host',
            'basePath' => '$ref: domain.yaml#path',

            'securityDefinitions'    => [
                'Bearer' => [
                    'in'          => 'header',
                    'type'        => 'apiKey',
                    'scheme'      => 'bearer',
                    'name'        => 'Authorization',
                    'description' => 'Authorization header <b>Bearer {token}</b>',
                ],
            ],

            'cache' => [
                'caching'  => false,
            ],
        ];
    }
}
