<?php

namespace Seat\Services\Listeners;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Events\MigrationsEnded;
use Seat\Services\Services\DeferredMigrationRegistry;

class RunDeferredMigrations
{
    /**
     * @throws BindingResolutionException
     */
    public function handle(MigrationsEnded $event) {
        $registry = app()->make(DeferredMigrationRegistry::class);

        $registry->runMigrations();
    }
}