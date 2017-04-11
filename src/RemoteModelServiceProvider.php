<?php

namespace LushDigital\MicroServiceRemoteModels;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class RemoteModelServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'LushDigital\MicroServiceRemoteModels\Events\RelationshipModified' => [
            'LushDigital\MicroServiceRemoteModels\Listeners\RelationshipModifiedListener',
        ],
    ];
}
