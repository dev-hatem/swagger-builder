<?php

namespace Creatify\SwaggerBuilder\Builders;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Creatify\SwaggerBuilder\BaseDocs;

class JsonBuilder
{
    use BaseDocs;

    private function save(string $path, mixed $data)
    {

        $path = $this->configurations['save_dir'] . DIRECTORY_SEPARATOR . $path;

        if (!File::exists(dirname($path)))
            File::makeDirectory(dirname($path),0775, true);

        File::put($path, json_encode($data,  JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}
