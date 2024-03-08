<?php

    namespace LaravelMonite\app\Providers;

    use Illuminate\Support\ServiceProvider;
    use LaravelMonite\Providers\DataTransferObjectMakeCommand;
    use function LaravelMonite\Providers\config_path;


    final class LaravelMoniteProvider extends ServiceProvider
    {
        public function boot()
        {

            $this->publishes([
                __DIR__.'/../config/laravel-monite.php' => config_path('laravel-monite.php'),
            ]);

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            if ($this->app->runningInConsole()) {
                $this->commands(
                    commands: [
                        DataTransferObjectMakeCommand::class,
                    ],
            );
            }
        }
    }
