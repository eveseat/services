<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Services\Seeding;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

abstract class AbstractScheduleSeeder extends Seeder
{
    /**
     * Returns an array of schedules that should be seeded whenever the stack boots up.
     *
     * @return array
     */
    abstract public function getSchedules(): array;

    /**
     * Returns a list of commands to remove from the schedule.
     *
     * @return array
     */
    abstract public function getDeprecatedSchedules(): array;

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
            if (! DB::table('schedules')->where('command', $job['command'])->exists()) {
                DB::table('schedules')->insert($job);
            }
        }

        // drop deprecated commands
        DB::table('schedules')->whereIn('command', $this->getDeprecatedSchedules())->delete();
    }
}
