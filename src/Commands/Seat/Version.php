<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Services\Commands\Seat;

use Illuminate\Console\Command;
use Seat\Services\Traits\VersionsManagementTrait;

/**
 * Class Version.
 * @package Seat\Services\Commands\Seat
 */
class Version extends Command
{
    use VersionsManagementTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:version {--offline : Skip Checking Github for latest versions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all of the SeAT component versions';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $offline = $this->option('offline');

        if ($offline)
            $this->info('Checking Local Versions Only');
        else
            $this->info('Checking Local and Latest Versions. Please wait...');

        $this->table(['Package Name', 'Local Version', 'Latest Version'],
            $this->getPluginsMetadataList()->core->map(function ($package) use ($offline) {
                if ($offline) {

                    return [
                        $package->getName(),
                        $package->getVersion(),
                        'Offline',
                    ];
                }

                return [
                    $package->getName(),
                    $package->getVersion(),
                    $this->getPackageLatestVersion($package->getPackagistVendorName(), $package->getPackagistPackageName()),
                ];
            }));
    }
}
