<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('database.default') === 'sqlite') {
            try {
                DB::statement('PRAGMA journal_mode=WAL;');
                DB::statement('PRAGMA synchronous=NORMAL;');
                DB::statement('PRAGMA cache_size=-20000;');
            } catch (\Throwable $e) {
                // Ignore if database file is not yet initialized
            }
        }

        // Warn in laravel.log instead of crashing the page when a view lazy-loads a relationship
        Model::preventLazyLoading(!app()->isProduction());
        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            $class = get_class($model);
            Log::warning("Lazy loading detected: [{$relation}] on [{$class}]");
        });
    }
}