<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

use Illuminate\Support\Collection;
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
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationMemberTracking(int $corporation_id): Collection
    {

        return CorporationMemberTracking::where('corporation_id', $corporation_id)
            ->leftJoin('refresh_tokens', 'refresh_tokens.character_id', '=', 'corporation_member_trackings.character_id')
            ->select('corporation_id', 'corporation_member_trackings.character_id', 'start_date', 'base_id',
                'logon_date', 'logoff_date', 'location_id', 'ship_type_id')
            ->selectRaw('case when isnull(token) then 0 else 1 end as key_ok')
            ->get();

    }
}
