<?php

namespace Appeto\EntityGenerator;

use Illuminate\Support\ServiceProvider;

class EntityGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Appeto\EntityGenerator\Commands\InstallCommand::class,
                \Appeto\EntityGenerator\Commands\MakeEntityCommand::class,
            ]);
        }
    }
}
