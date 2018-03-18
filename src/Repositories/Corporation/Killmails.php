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

use Seat\Eveapi\Models\Killmails\CorporationKillmail;

trait Killmails
{
    /**
     * Return the Killmails for a Corporation.
     *
     * @param int  $corporation_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCorporationKillmails(
        int $corporation_id, bool $get = true, int $chunk = 200)
    {

        $killmails = CorporationKillmail::select(
            '*',
            'corporation_killmails.corporation_id as ownerID',
            'killmail_victims.character_id as victimID')
            ->leftJoin(
                'killmail_details',
                'corporation_killmails.killmail_id', '=',
                'killmail_details.killmail_id')
            ->leftJoin(
                'killmail_victims',
                'killmail_victims.killmail_id', '=',
                'corporation_killmails.killmail_id'
            )
            ->leftJoin(
                'invTypes',
                'killmail_victims.ship_type_id', '=',
                'invTypes.typeID')
            ->leftJoin('mapDenormalize',
                'killmail_details.solar_system_id', '=',
                'mapDenormalize.itemID')
            ->where('corporation_killmails.corporation_id', $corporation_id);

        if ($get)
            return $killmails->orderBy('corporation_killmails.killmail_id', 'desc')
                ->paginate($chunk);

        return $killmails;

    }
}
