## Lumen API Command Resource Api

Automatically create resource api, model,router.


`php artisan resource:create {route_name} {--version_route=default} {--controller=null}`


https://github.com/minhngoc2512/resource-api-lumen
## Installation

Run command update package:

```sh
   $ composer require minhngoc/resource-api-lumen:dev-master
```

Go to your `bootstrap/app.php` and register the service provider:

```php

$app->register(MinhNgoc\ResourceApiLumen\ResourceCommandServiceProvider::class);
```


### Available command options:

Option | Description
--------- | -------
`version_route` | Version api
`controller` | Controller
`route_name` | Route name
