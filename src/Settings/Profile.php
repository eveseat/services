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

namespace Seat\Services\Settings;

use Seat\Services\Models\UserSetting;

/**
 * Class Profile.
 *
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

        'sidebar' => ['sidebar-full', 'sidebar-collapse'],
        'thousand_seperator' => [' ', ',', '.'],
        'decimal_seperator' => [',', '.'],
        'mail_threads' => ['yes', 'no'],
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
}
