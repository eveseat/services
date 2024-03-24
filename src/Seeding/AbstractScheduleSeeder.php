<?php

namespace Seat\Services\Seeding;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

abstract class AbstractScheduleSeeder extends Seeder
{
    /**
     * Returns an array of schedules that should be seeded whenever the stack boots up
     *
     * @return array
     */
    abstract function getSchedules(): array;

    /**
     * Returns a list of commands to remove from the schedule
     * @return array
     */
    abstract function getDeprecatedSchedules(): array;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Check if we have the schedules, else, insert them
        $schedules = $this->getSchedules();
        foreach ($schedules as $job) {
            if (!DB::table('schedules')->where('command', $job['command'])->exists()) {
                DB::table('schedules')->insert($job);
            }
        }

        // drop deprecated commands
        DB::table('schedules')->whereIn('command', $this->getDeprecatedSchedules())->delete();
    }
}