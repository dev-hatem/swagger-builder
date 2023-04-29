<?php

\Illuminate\Support\Facades\Route::get('/documentation',  function (){
    $path = explode('public', config('swagger-builder.save_dir'));

    $url  = sprintf('%s/%s.%s', asset(str_replace(DIRECTORY_SEPARATOR, '/', $path[1])), config('swagger-builder.docs_file_name'), config('swagger-builder.default_format'));

    return view('documentation', compact('url'));
});
