<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    App\Module\Customers\Providers\RepositoryServiceProvider::class,
];
