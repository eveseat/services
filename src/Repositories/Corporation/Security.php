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

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Corporation\CorporationMemberTracking;
use Seat\Eveapi\Models\Corporation\CorporationRoleHistory;
use Seat\Eveapi\Models\Corporation\CorporationTitle;

/**
 * Class Security.
 * @package Seat\Services\Repositories\Corporation
 */
trait Security
{
    /**
     * Get the Member Security for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationMemberRoles(int $corporation_id): Collection
    {

        return CorporationMemberTracking::with('roles', 'character')
            ->where('corporation_id', $corporation_id)
            ->get();
    }

    /**
     * Get the security change logs for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationMemberSecurityLogs(int $corporation_id): Collection
    {

        return CorporationRoleHistory::where('corporation_id', $corporation_id)
            ->latest()
            ->get();
    }

    /**
     * Get the titles for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationMemberSecurityTitles(int $corporation_id): Collection
    {

        return CorporationTitle::with('characters')
                               ->where('corporation_id', $corporation_id)
                               ->get();
    }
}
