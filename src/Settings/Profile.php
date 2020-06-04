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

namespace Seat\Services\Settings;

use Seat\Services\Models\UserSetting;

/**
 * Class Profile.
 * @package Seat\Services\Settings
 */
class Profile extends Settings
{
    /**
     * The options available for this Setting type.
     *
     * @var array
     */
    public static $options = [

        'sidebar'            => ['sidebar-full', 'sidebar-collapse'],
        'skins'              => [
            'skin-blue', 'skin-black', 'skin-purple', 'skin-green',
            'skin-red', 'skin-yellow', 'skin-blue-light', 'skin-black-light',
            'skin-purple-light', 'skin-green-light', 'skin-red-light',
            'skin-yellow-light',
        ],
        'thousand_seperator' => [' ', ',', '.'],
        'decimal_seperator'  => [',', '.'],
        'mail_threads'       => ['yes', 'no'],
    ];

    /**
     * @var string
     */
    protected static $prefix = 'profile';

    /**
     * @var
     */
    protected static $model = UserSetting::class;

    /**
     * @var string
     */
    protected static $scope = 'user';

    /**
     * @var array
     */
    protected static $defaults = [

        // UI
        'sidebar'             => 'sidebar-full',
        'skin'                => 'skin-black',
        'language'            => 'en',
        'mail_threads'        => 'yes',

        // A groups main character_id
        'main_character_id'   => 0,

        // Numbers
        'thousand_seperator'  => ' ',
        'decimal_seperator'   => '.',

        // Notifications
        'email_notifications' => 'no',
        'email_address'       => '',

        // Multi factor authentication
        'require_mfa'         => 'no',
    ];
}
