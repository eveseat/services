<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Seat\Services\Commands\Seat\Admin\Email;
use Seat\Services\Commands\Seat\Version;
use Seat\Services\Models\UserSetting;
use Seat\Services\Models\UserSettingExtension;
use Seat\Services\Services\ModelExtensionRegistry;

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

        // Register commands
        $this->addCommands();

        // Inform Laravel how to load migrations
        $this->add_migrations();

        $this->app->make(ModelExtensionRegistry::class)->registerExtension(UserSetting::class,UserSettingExtension::class,"user");

        Artisan::command("service:test",function (){
            $setting = UserSetting::first();
            dd(json_encode($setting),json_encode($setting->user()->get()));
        });
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

        $this->app->singleton(ModelExtensionRegistry::class, function (){
            return new ModelExtensionRegistry();
        });
    }

    private function addCommands()
    {
        $this->commands([
            Email::class,
            Version::class,
        ]);
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
}
