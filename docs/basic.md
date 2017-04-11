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

| Property               | Description                                                                                          |
|------------------------|------------------------------------------------------------------------------------------------------|
| `$restEndpoints`       | List of endpoints. One for each HTTP method. If not defined then the plural model name will be used. |
| `$https`               | Should requests for this model be made using https.                                                  |
| `$primaryKeyAttribute` | The name of the attribute to use as the primary key. Defaults to 'id'.                               |
| `$pluralName`          | The plural model name.                                                                               |
| `$attributeCacheKeys`  | A list of the model attributes that can be used as cache keys.                                       |

## Using a Model
Once you have created your model it can then be used in a very similar way to a standard [Eloquent](https://laravel.com/docs/5.4/eloquent)
model. Some brief examples:

### Create a new model
```php
$data = [
    'name' => 'My Awesome Shop',
    'country_code' => 'GB',
];

$shop = new Shop;
$shop->fill($data);
$shop->save();
```

### Setting an attribute
Via magic setter:
```php
$shop = new Shop;
$shop->name = 'My Awesome Shop';
```

Or explicitly:
```php
$shop = new Shop;
$shop->setAttribute('name', 'My Awesome Shop');
```

For more detailed documentation on manipulating models see the other docs:

* [CRUD](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/crud.md)
* [Relations](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/relations.md)
* [Conditions](https://github.com/LUSHDigital/microservice-remote-models/tree/master/docs/conditions.md)