<?php

namespace Seat\Services\Services;

use Closure;

class DeferredMigrationRegistry
{
    /**
     * @var array<Closure>
     */
    protected array $deferred_migrations = [];

    public function schedule(Closure $migration): void {
        $this->deferred_migrations[] = $migration;
    }

    public function runMigrations(): void {
        logger()->info(sprintf("[Deferred Migrations] Running %d deferred migrations", count($this->deferred_migrations)));

        foreach ($this->deferred_migrations as $migration){
            $migration();
        }
    }
}