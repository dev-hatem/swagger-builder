<?php

namespace Creatify\SwaggerBuilder\Builders;

use Creatify\SwaggerBuilder\BaseDocs;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class YamlBuilder
{
    use BaseDocs;

    private function save(string $path, mixed $data)
    {
        $path = $this->configurations['save_dir'] . DIRECTORY_SEPARATOR . $path;

        if (!File::exists(dirname($path)))
            File::makeDirectory(dirname($path),0775, true);

        File::put($path, Yaml::dump($data, 20, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }
}
