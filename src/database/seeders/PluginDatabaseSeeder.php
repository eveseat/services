<?php
 
namespace Seat\Services\Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
 
class PluginDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $seeders = config('seat.seeders', []);

        $this->command->info('Running all unique registered seeders');
        $this->call(array_unique($seeders));
        $this->command->info('Registered seeders run complete');

    }
}