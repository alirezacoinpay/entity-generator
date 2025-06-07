<?php

namespace Alirezappeto\EntityGenerator;

use Illuminate\Support\ServiceProvider;

class EntityGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Alirezappeto\EntityGenerator\Commands\InstallCommand::class,
                \Alirezappeto\EntityGenerator\Commands\MakeEntityCommand::class,
            ]);
        }
    }
}
