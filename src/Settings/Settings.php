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

namespace Seat\Services\Settings;

use Illuminate\Support\Facades\Cache;
use Seat\Services\Exceptions\SettingException;

/**
 * Class Settings.
 * @package Seat\Services\Settings
 */
abstract class Settings
{
    /**
     * The prefix used in the cache.
     *
     * @var
     */
    protected static $prefix;

    /**
     * The FQN to the model with the settings.
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
     * a user setting.
     *
     * @var string
     */
    protected static $scope = 'global';

    /**
     * Retrieve a setting by name.
     *
     * @param      $name
     * @param null $for_id
     *
     * @return mixed
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public static function get($name, $for_id = null)
    {

        // Pickup the value from the cache if we can. Otherwise,
        // read the value for the database and cache it for next
        // time.
        return Cache::rememberForever(self::get_key_prefix($name,
            self::get_affected_id($for_id)), function () use ($name, $for_id) {

            // Init a new MODEL
            $value = (new static::$model);

            // If we are not in the global scope, add a constraint
            // to be user user specific.
            if (static::$scope != 'global')
                $value = $value->where('user_id', self::get_affected_id($for_id));

            // Retrieve the value
            $value = $value->where('name', $name)
                ->value('value');

            if ($value)
                return json_decode($value);

            // If we have no value, check if we can return
            // a default setting
            if (array_key_exists($name, static::$defaults))
                return json_decode(json_encode(static::$defaults[$name]));

            return null;
        });
    }

    /**
     * Determine the unique prefix for the key by name.
     *
     * @param $name
     * @param $for_id
     *
     * @return string
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public static function get_key_prefix($name, $for_id = null)
    {

        // Ensure we have a prefix to work with.
        if (is_null(static::$prefix))
            throw new SettingException('No prefix defined. Have you extended and declared $prefix?');

        // Prefix user keys with user_id
        if (static::$scope != 'global')
            return implode('.', [$for_id, static::$prefix, $name]);

        // Global keys only with the global prefix.
        return implode('.', [static::$prefix, $name]);
    }

    /**
     * Determine the effected user id.
     *
     * If this is for a specific id use that, otherwise
     * assume the currently logged in user's id. If we
     * dont have an already logged in session, well then
     * we make the $for_id null.
     *
     * @param $for_id
     *
     * @return int|null
     */
    public static function get_affected_id($for_id)
    {

        // Without auth, return what we have.
        if (! auth()->check())
            return $for_id;

        if (is_null($for_id))
            return auth()->user()->id;

        return $for_id;
    }

    /**
     * @param      $name
     * @param      $value
     * @param null $for_id
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public static function set($name, $value, $for_id = null)
    {

        // Init a new MODEL
        $db = (new static::$model);

        // If we are not in the global scope, add a constraint
        // to be user specific.
        if (static::$scope != 'global')
            $db = $db->where('user_id', self::get_affected_id($for_id));

        // Retrieve the value
        $db = $db->where('name', $name)
            ->first();

        // By default, json encode values.
        $encoded_value = json_encode($value, JSON_NUMERIC_CHECK);

        // Check if we have a value, else create a new
        // instance
        if (! $db)
            $db = new static::$model;

        $db->fill([
            'name'  => $name,
            'value' => $encoded_value,
        ]);

        // Again, if we are not in the global context, then
        // we need to constrain this setting to a user.
        if (static::$scope != 'global')
            $db->user_id = self::get_affected_id($for_id);

        $db->save();

        // Update the cached entry with the new value
        Cache::forever(self::get_key_prefix($name, self::get_affected_id($for_id)), $value);
    }
}
