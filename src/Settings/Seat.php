<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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

use Seat\Services\Models\GlobalSetting;

/**
 * Class Seat
 * @package Seat\Services\Settings
 */
class Seat extends Settings
{

    /**
     * The options available for this Setting type
     *
     * @var array
     */
    public static $options = [

        'registration'   => ['yes', 'no'],
        'force_min_mask' => ['yes', 'no'],
        'allow_sso'      => ['yes', 'no'],
        'allow_tracking' => ['yes', 'no'],
    ];

    /**
     * @var string
     */
    protected static $prefix = 'seat';

    /**
     * @var
     */
    protected static $model = GlobalSetting::class;

    /**
     * @var string
     */
    protected static $scope = 'global';

    /**
     * @var array
     */
    protected static $defaults = [

        // User Registration
        'registration'    => 'yes',

        // Allow users to login using EVE Online SSO
        'allow_sso'       => 'no',

        // Admin contact
        'admin_contact'   => 'seatadmin@localhost.local',

        // Force the Minimum Access Mask for Keys
        'force_min_mask'  => 'no',

        // Minimum required Access Mask for Keys
        'min_access_mask' => 0,

        // Allow Usage Tracking
        'allow_tracking'    => 'yes',
    ];

}
