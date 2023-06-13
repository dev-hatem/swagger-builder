![pacakge](https://github.com/dev-hatem/swagger-builder/blob/master/screenshots/docs.png)  

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
```
```php
Creatify\SwaggerBuilder\Providers\SwaggerBuilderServiceProvider::class,
```

```php
# publish the needed files
```
```php
php artisan vendor:publish --provider="Creatify\SwaggerBuilder\Providers\SwaggerBuilderServiceProvider"
```
```php
php artisan vendor:publish --tag=swagger-builder
```

##### run the below command to start build your docs and select file format
```php
php artisan swagger:build
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

1. Manage most used end points like:
 * page
 * index
 * store
 * show
 * delete
 * update
 * restore
 * force delete
2. security is optional
3. suggestion to the expected route path or can enter it
4. detect the params from the given route path
5. handle sorting, pagination length, search
6. generate separate file for the given model for all above opetations
7. support json and yaml
8. can extend the above operations and add others
9. have the route `/documentation` to view the generated docs
10. supportes to multiple servers url from the config file `swagger-builder.php`

![pacakge](https://github.com/dev-hatem/swagger-builder/blob/master/screenshots/Screenshot%202023-04-29%20153515.png)  

![pacakge](https://github.com/dev-hatem/swagger-builder/blob/master/screenshots/Screenshot%202023-04-29%20153536.png)  

![pacakge](https://github.com/dev-hatem/swagger-builder/blob/master/screenshots/Screenshot%202023-04-29%20153701.png)  






## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
