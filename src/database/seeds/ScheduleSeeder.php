<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Services\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class ScheduleSeeder.
 * @package Seat\Services\database\seeds
 */
class ScheduleSeeder extends Seeder
{
    /**
     * @var array
     */
    protected $schedule = [

        [   // ESI Status | Every Minute
            'command'           => 'esi:update:status',
            'expression'        => '* * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Horizon Metrics | Every Five Minutes
            'command'           => 'horizon:snapshot',
            'expression'        => '*/5 * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // EVE Server Status | Every Five Minutes
            'command'           => 'eve:update:status',
            'expression'        => '*/5 * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // SDE Data | Monthly
            'command'           => 'eve:update:sde',
            'expression'        => '0 0 1 * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // EVE Map | Daily at 12am
            'command'           => 'esi:update:public',
            'expression'        => '0 0 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Characters | Hourly
            'command'           => 'esi:update:characters',
            'expression'        => '0 * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Corporations | Every two hours
            'command'           => 'esi:update:corporations',
            'expression'        => '0 */2 * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Killmails | Every fifteen minutes
            'command'           => 'esi:update:killmails',
            'expression'        => '*/15 * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
        [   // Contracts | Every fifteen minutes
            'command'           => 'esi:update:contracts',
            'expression'        => '*/15 * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Check if we have the schedules, else,
        // insert them
        foreach ($this->schedule as $job) {

            if (! DB::table('schedules')->where('command', $job['command'])->first())
                DB::table('schedules')->insert($job);
        }
    }
}
