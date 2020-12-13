<?php

namespace Binarcode\LaravelDeveloper;

use Binarcode\LaravelDeveloper\Commands\PruneCommand;
use Binarcode\LaravelDeveloper\Models\ExceptionLog;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;

class LaravelDeveloperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-developer.php' => config_path('developer.php'),
            ], 'developer-config');

            $migrationFileName = 'create_laravel_developer_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ], 'developer-migrations');
            }

            $this->commands([
                PruneCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-developer.php', 'developer');

        $this->app->singleton(ExceptionLog::class, function () {
            return config('laravel-developer.exception_log_model', ExceptionLog::class);
        });
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
