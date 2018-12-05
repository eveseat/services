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

namespace Seat\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class ServicesServiceProvider extends ServiceProvider
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

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations'),
        ]);
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
}
