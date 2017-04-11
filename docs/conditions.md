# Conditions
This page gives examples on how to use conditions when retrieving records from a remote service.

## Pre-requisites
To be able to query based on a condition, the remote service needs to have a matching endpoint for this condition.

For example if I wanted to get shops by the `country` field the shops remote service will need to have an endpoint with
the following URL signature: `http://shops/shops/country/{country}` (where `{country}` is the value of our condition).

## Usage
To retrieve a model based on a condition you can use the static `where` method on the model:
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
     * @param string $country
     * @return []
     */
    public function byCountry($country)
    {
        $shops = Shop::where('country', $country)
            ->toArray();
        
        return !empty($shops) ? $shops : null;
    }
}
```