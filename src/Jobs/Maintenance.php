<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Services\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterRole;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\FailedJob;
use Seat\Eveapi\Models\Status\EsiStatus;
use Seat\Eveapi\Models\Status\ServerStatus;
use Seat\Web\Models\Group;
use Seat\Web\Models\User;

/**
 * Class Maintenance.
 * @package Seat\Services\Jobs
 */
class Maintenance implements ShouldQueue
{

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Perform the maintenance job.
     */
    public function handle()
    {

        $this->cleanup_tables();

        if (setting('cleanup_data', true) == 'yes')
            $this->cleanup_stale_data();
    }

    /**
     * Partially truncates tables that typically contain
     * a lot of data.
     */
    public function cleanup_tables()
    {

        logger()->info('Performing table maintenance');

        // Prune the failed jobs table
        FailedJob::where('id', '<', (FailedJob::max('id') - 100))->delete();

        // Prune the server statuses older than a week.
        ServerStatus::where('created_at', '<', carbon('now')->subWeek(1))->delete();

        // Prune ESI statuses older than a week
        EsiStatus::where('created_at', '<', carbon('now')->subWeek(1))->delete();

        // Remove groups with no users
        Group::doesntHave('users')->delete();
    }

    /**
     * Cleans up stale data that relate to characters and
     * corporations that no longer have valid users.
     */
    public function cleanup_stale_data()
    {

        logger()->info('Performing stale data maintenance');

        // First cleanup characters.
        CharacterInfo::whereNotIn('character_id', function ($query) {

            $query->select('id')
                ->from((new User)->getTable());

        })->each(function ($character) {

            logger()->info('Cleaning up character: ' . $character->name);
            $character->delete();

        });

        // Next, cleanup corporations
        CorporationInfo::whereNotIn('corporation_id', function ($query) {

            // Filter out corporations that we have characters for.
            $query->select('corporation_id')
                ->from((new CharacterInfo)->getTable())
                ->whereIn('character_id', function ($sub_query) {

                    // Ensure that its characters with roles. Otherwise
                    // the corporation info is meaningless anyways.
                    $sub_query->select('character_id')
                        ->from((new CharacterRole)->getTable());

                });

        })->each(function ($corporation) {

            dump('Cleaning up corporation: ' . $corporation->name);
            logger()->info('Cleaning up corporation: ' . $corporation->name);
            $corporation->delete();

        });
    }
}
