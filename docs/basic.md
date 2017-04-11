# Basic Usage
This page provides a quick introduction to Remote Models and introductory examples.
If you have not already installed the package please read the [README](https://github.com/LUSHDigital/microservice-remote-models#installation).

## Creating a Model
The starting point for any usage of this package is to create a new remote model. You do this by simply creating a new
class which extends `LushDigital\MicroServiceRemoteModels\Model`.

```php
<?php

namespace App\RemoteModels;

use LushDigital\MicroServiceRemoteModels\Model;

class Shop extends Model
{
    /**
     * The base URI for this model to make requests against.
     *
     * @var string
     */
    protected $baseUri = 'shops';
}
```

The only required property to define in the model is `$baseUri`. This is the DNS name of the microservice that powers
this model. The other properties that can be defined are:

|     Property     |                                              Description                                             |
|:----------------:|:----------------------------------------------------------------------------------------------------:|
| `$restEndpoints` | List of endpoints. One for each HTTP method. If not defined then the plural model name will be used. |