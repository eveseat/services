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

namespace Seat\Services\Repositories\Corporation;

use Illuminate\Database\Eloquent\Builder;
use Seat\Eveapi\Models\Corporation\CorporationMemberTracking;

/**
 * Class Members.
 * @package Seat\Services\Repositories\Corporation
 */
trait Members
{
    /**
     * Return the Member Tracking for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getCorporationMemberTracking(int $corporation_id): Builder
    {

        return CorporationMemberTracking::with(
            'user',
            'user.refresh_token',
            'type'
            )
            ->where('corporation_id', $corporation_id);

    }
}
