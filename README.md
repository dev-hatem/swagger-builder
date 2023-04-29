# Swagger Builder

Generate awesome swagger docs for your apis from php array

## Installation

Use the package manager [composer](https://getcomposer.org/) to install foobar.

```bash
composer require creatify/swagger-builder
```

## Usage

```php
# register the package service provider in config/app.php in providers array

Creatify\SwaggerBuilder\Providers\SwaggerBuilderServiceProvider::class,
```
```php
# run the below command to start build your docs

php artisan swagger:build

# then select your document format [YAML - JSON]
```
```txt
# make new directory to save the php array which contain the structure of your endpoints
 by default the path in public/swagger/endpoints
```
```txt
# make new php file which represents your endpoints with the below structure
```
```php

<?php

return [

    'schema' => [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string'
    ],

    'store' => [
        'name' => 'string',
        'description' => 'string'
    ],

    'update' => [
        'name' => 'string',
        'description' => 'string'
    ]
];

#schema represent the single response object of your model
#store contains the needed data to store new model (store method request body)
#update contains the needed data to update existing model (update method request body)
```

```php
#start generating 
php artisan swagger:generate
```




## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)
