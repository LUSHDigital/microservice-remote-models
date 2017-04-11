# Lush Digital - Microservice Remote Models
A Lumen package to provide a familiar model paradigm but for distributed data.

## Description
This package provides a model system, similar to Eloquent, but with the key focus on distributed data. Each model class
is bound to a remote microservice with an expected RESTful API. This allows developers to create service aggregators which
can manipulate data, via models, just as easily as if the data was in a local database.

TODO: Add API contract for RESTful service.

## Package Contents

## Installation
Install the package as normal:

```bash
$ composer require lushdigital/microservice-remote-models
```

Register the service provider with Lumen in the bootstrap/app.php file:

```php
$app->register(LushDigital\MicroServiceRemoteModels\RemoteModelServiceProvider::class);
```

The package requires that the following changes are made to the Lumen config in bootstrap/app.php
```php
<?php

// Uncomment the line below to enable Facade support.
$app->withFacades();

// Uncomment the line below to enable Eloquent ORM support.
$app->withEloquent();

// Add the line below to load database config. This is required for caching to work.
$app->configure('database');
```

## Usage
TODO: Documentation.