<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder|CorporationKillmail
     */
    public function getCorporationKillmails(int $corporation_id)
    {

        return CorporationKillmail::with(
            'killmail_detail',
            'killmail_detail.solar_system',
            'killmail_victim',
            'killmail_victim.ship_type',
            'killmail_victim.victim_character',
            'killmail_victim.victim_corporation',
            'killmail_victim.victim_alliance')
            ->where('corporation_killmails.corporation_id', $corporation_id);

    }
}
