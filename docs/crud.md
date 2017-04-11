# CRUD
This page provides examples on how to use remote models in a typical CRUD (Create Read Update Delete) pattern.

Note: We're assuming here that you're operating within a typical Lumen controller.

## Create
To create a new record in the remote service, just create a new model instance, populate the data and call the `save` method.
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
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
        $shop->save();
    }
}
```

## Read
To get an existing record from the remote service, just call the static `find` method with the primary key value as a parameter.
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
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
        $shop = Shop::find($id);
        return !empty($shop) ? $shop : null;
    }
}
```

## Update
To update an existing record in the remote service, create a new model instance, set the primary key, amend any data
you want and then call the `save` method.
```php
<?php

namespace App\Http\Controllers;

use App\Shop;
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
        
        $shop->save();
    }
}
```

## Delete
To delete an existing record from a remote service just call the static `delete` method on the model, with the primary
key value as a parameter.

```php
<?php

namespace App\Http\Controllers;

use App\Shop;
use Illuminate\Http\Request;
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
        Shop::delete($id);
    }
}
```