# Relations
This page provides examples on how to use remote models with relations in typical usage scenarios.

## Defining Relations
The remote model class provides a static `with` method which can be used to define the models to consider as relations.
This method accepts either a single fully-qualified class name (or an array of class names) for a related remote model.

### Examples
Defining a single relationship:
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use App\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Get a single shop instance.
     *
     * @param int $id
     */
    public function show($id)
    {
        //...
        Shop::with(Address::class);
        //...
    }
}
```

Defining multiple relationships:
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use App\Address;
use App\OpeningTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Get a single shop instance.
     *
     * @param int $id
     */
    public function show($id)
    {
        //...
        $relations = [
            Address::class,
            OpeningTime::class,
        ];
        Shop::with($relations);
        //...
    }
}
```

## CRUD
Just like with a single remote model, you can use relations in a CRUD pattern too. This allows not only the main model,
but all the related models to be modified too.

### Create
To create a new record (and it's relations) in the remote service, just create a new model instance, populate the data,
add any relations, and call the `save` method.
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use App\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Create a new shop instance.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        // Validate the request...

        $shop = new Shop;
        $shop->name = $request->input('name');
        
        foreach ($request->input('addresses') as $input_address) {
            $address = new Address;
            $address->fill($input_address);

            $shop->addRelation($address);
        }
        
        $shop->save();
    }
}
```

### Read
To get an existing record (and it's relations) from the remote service, just call the static `with` method to define the
relations and then the `find` method with the primary key value as a parameter.
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use App\Address;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Get a single shop instance.
     *
     * @param int $id
     * @return Shop|null
     */
    public function show($id)
    {
        $shop = Shop::with(Address::class)
            ->fetch($id)
            ->toArray();
        
        return !empty($shop) ? $shop : null;
    }
}
```

### Update
To update an existing record (and it's relations) in the remote service, create a new model instance, set the primary
key, amend any data you want, add the relations, and then call the `save` method.
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use App\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Update the specified shop.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request...
        
        $shop = new Shop;
        $shop->setPrimaryKeyValue($id);
        $shop->name = $request->input('name');
        
        foreach ($request->input('addresses') as $input_address) {
            $address = new Address;
            $address->fill($input_address);

            $shop->addRelation($address);
        }
        
        $shop->save();
    }
}
```

### Delete
To delete an existing record (and it's relations) from a remote service just call the static `with` method to define the
relations and then the `delete` method, with the primary key value as a parameter.

```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use App\Address;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Delete the specified shop.
     *
     * @param int $id
     */
    public function destroy($id)
    {
        Shop::with(Address::class)->delete($id);
    }
}
```

### Detach Relations
If you do not want to actually delete the related entities, but just remove the relationship you can use the 'detach' functionality.
To do this you can use the `detachRelations` method:

```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use App\Address;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * Detach addresses from the specified shop.
     *
     * @param int $id
     */
    public function detachAddresses($id)
    {        
        $shop = new Shop;
        $shop->setPrimaryKeyValue($id);
        $shop->detachRelations(Address::class);
    }
}
```