# Lush Digital - Microservice Remote Models
A Lumen package to provide a familiar model paradigm for distributed data.

## Description
This package provides a model system, similar to Eloquent, but with the key focus on distributed data. Each model class
is bound to a remote microservice with an expected RESTful API. This allows developers to create service aggregators which
can manipulate data, via models, just as easily as if the data was in a local database.

To allow this to work the package is quite opinionated on the request/response data in the remote services. This data
should always conform to the following standard:

https://github.com/LUSHDigital/microservice-core/blob/master/spec/swagger.yaml

> This package is intended to operate within a Kubernetes cluster whereby service discovery is handled by DNS names.

### Relationships
The package also handles relationships between remote models via the use of gRPC. As well as the microservices existing
to power the remote models, it is also expected that a gRPC application will be available to manage the relationships.
This gRPC application is based upon this [protocol buffer](https://github.com/LUSHDigital/lush-global-soa-architecture/blob/feature/SOA-66/protos/relationship/v1/relationship.proto).

See the [Configuration](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/config.md) documentation for information on DNS and ports.

## Installation
Install the package as normal:

```bash
$ composer require lushdigital/microservice-remote-models
```

Register the service provider with Lumen in the bootstrap/app.php file:

```php
$app->register(LushDigital\MicroServiceRemoteModels\RemoteModelServiceProvider::class);
```

The package requires that the following changes are made to the Lumen config in `bootstrap/app.php`
```php
<?php

// Uncomment the line below to enable Facade support.
$app->withFacades();

// Uncomment the line below to enable Eloquent ORM support.
$app->withEloquent();

// Add the line below to load database config. This is required for caching to work.
$app->configure('database');
```

## Documentation
* [Basic usage](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/basic.md)
* [CRUD](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/crud.md)
* [Relations](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/relations.md)
* [Conditions](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/conditions.md)
* [Configuration](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/config.md)