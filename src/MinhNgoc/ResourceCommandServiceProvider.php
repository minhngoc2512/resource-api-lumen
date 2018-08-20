<?php

namespace MinhNgoc\ResourceApiLumen;

use Illuminate\Support\ServiceProvider;

class ResourceCommandServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands('MinhNgoc\ResourceApiLumen\CreateResourceApi');
    }
}
