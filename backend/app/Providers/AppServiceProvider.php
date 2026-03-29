<?php

namespace App\Providers;

use Carbon\CarbonImmutable;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Model::shouldBeStrict();
        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            $class = get_class($model);
            if (app()->isLocal()) {
                throw new LazyLoadingViolationException($model, $relation);
            }
            info('Attempted to lazy load "' . $relation . '" on model "' . $class . '"');
        });

        DB::prohibitDestructiveCommands(app()->isProduction());

        //USE IMMUTABLE DATES EXAMPLE: DATE::now() RETURNS A CARBONIMMUTABLE INSTANCE INSTEAD OF A CARBON INSTANCE
        Date::use(CarbonImmutable::class);



    }
}
