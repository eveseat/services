<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Services\Settings;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Seat\Services\Exceptions\SettingException;

/**
 * Class Settings
 * @package Seat\Services\Settings
 */
abstract class Settings
{

    /**
     * The prefix used in the cache
     *
     * @var
     */
    protected static $prefix;

    /**
     * The FQN to the model with the settings
     *
     * @var
     */
    protected static $model;

    /**
     * The array with default, fallback values.
     *
     * @var array
     */
    protected static $defaults = [];

    /**
     * Define if this is a global setting or
     * a user setting
     *
     * @var string
     */
    protected static $scope = 'global';

    /**
     * Retreive a setting by name.
     *
     * @param $name
     *
     * @return mixed
     */
    public static function get($name)
    {

        return Cache::rememberForever(
            self::get_key_prefix($name), function () use ($name) {

            // Init a new MODEL
            $value = (new static::$model);

            // If we are not in the global scope, add a constraint
            // to be user specific.
            if (static::$scope != 'global')
                $value = $value->where('user_id', auth()->user()->id);

            // Retreive the value
            $value = $value->where('name', $name)
                ->pluck('value');

            if ($value)
                return $value;

            // If we have no value, check if we can return
            // a default setting
            if (array_key_exists($name, static::$defaults))
                return static::$defaults[$name];

            return null;
        });
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public static function set($name, $value)
    {

        // Init a new MODEL
        $db = (new static::$model);

        // If we are not in the global scope, add a constraint
        // to be user specific.
        if (static::$scope != 'global')
            $db = $db->where('user_id', auth()->user()->id);

        // Retreive the value
        $db = $db->where('name', $name)
            ->first();

        // Check if we have a value, else create a new
        // instance
        if (!$db)
            $db = new static::$model;

        $db->fill([
            'name'  => $name,
            'value' => $value
        ]);

        // Again, if we are not in the global context, then
        // we need to constrain this setting to a user.
        if (static::$scope != 'global')
            $db->user_id = auth()->user()->id;

        $db->save();

        // Update the cached entry with the new value
        Cache::forever(self::get_key_prefix($name), $value);

        return;

    }

    /**
     * Determine the unique prefix for the key by name.
     *
     * @param $name
     *
     * @return string
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public static function get_key_prefix($name)
    {

        // Ensure we have a prefix to work with.
        if (is_null(static::$prefix))
            throw new SettingException(
                'No prefix defined. Have you extended and declared $prefix?');

        return implode('.', [Session::getId(), static::$prefix, $name]);
    }

}
