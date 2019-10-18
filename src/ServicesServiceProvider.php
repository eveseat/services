<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

namespace Seat\Services;

use App\Providers\AbstractSeatPlugin;
use Illuminate\Support\Facades\DB;

class ServicesServiceProvider extends AbstractSeatPlugin
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        // If we are in debug mode, listen to database events
        // and log queries to the log file.
        if (env('DB_DEBUG', false)) {

            DB::listen(function ($query) {

                $positional = 0;
                $full_query = '';

                foreach (str_split($query->sql) as $char) {

                    if ($char === '?') {

                        $value = $query->bindings[$positional];

                        if (is_scalar($value))
                            $full_query = $full_query . '"' . $value . '"';
                        else
                            $full_query = $full_query . '[' . gettype($value) . ']';

                        $positional++;

                    } else {

                        $full_query = $full_query . $char;

                    }
                }

                logger()->debug(' ---> QUERY DEBUG: ' . $full_query . ' <---');

            });
        }

        // Inform Laravel how to load migrations
        $this->add_migrations();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__ . '/Config/services.config.php', 'services.config');
    }

    /**
     * Set the path for migrations which should
     * be migrated by laravel. More informations:
     * https://laravel.com/docs/5.5/packages#migrations.
     */
    private function add_migrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'SeAT Services';
    }

    /**
     * Return the plugin repository address.
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/eveseat/services';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     */
    public function getPackagistPackageName(): string
    {
        return 'services';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     */
    public function getPackagistVendorName(): string
    {
        return 'eveseat';
    }

    /**
     * Return the plugin installed version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return config('services.config.version');
    }
}
