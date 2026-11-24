<?php
// FILE: app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Models\StudentActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        if (config('database.default') === 'sqlite') {
            try {
                DB::statement('PRAGMA journal_mode=WAL;');
                DB::statement('PRAGMA synchronous=NORMAL;');
                DB::statement('PRAGMA cache_size=-20000;');
            } catch (\Throwable $e) {
                // Ignore if database file is not yet initialized
            }
        }

        // Automatically log student logins and new registrations
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User && $event->user->role === 'student') {
                StudentActivityLog::record($event->user, 'Student Logged In', 'auth', 'Signed in to BrainBalance');
            }
        });

        Event::listen(Registered::class, function (Registered $event) {
            if ($event->user instanceof User && $event->user->role === 'student') {
                StudentActivityLog::record($event->user, 'Student Registered', 'auth', 'Created a new BrainBalance student account');
            }
        });

        // Warn in laravel.log instead of crashing the page when a view lazy-loads a relationship
        Model::preventLazyLoading(!app()->isProduction());
        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            $class = get_class($model);
            Log::warning("Lazy loading detected: [{$relation}] on [{$class}]");
        });
    }
}