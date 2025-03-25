<?php

namespace App\Providers;

use App\Services\ThirdParty\CreatorDBServiceInterface;
use App\Services\ThirdParty\CreatorDBServiceV1;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ThirdPartyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CreatorDBServiceInterface::class, function (Application $app) {
            return new CreatorDBServiceV1(config('influenser.creator_db.url') , config('influenser.creator_db.key'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
