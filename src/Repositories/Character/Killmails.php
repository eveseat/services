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

namespace Seat\Services\Repositories\Character;

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Killmails\CharacterKillmail;

/**
 * Class Killmails.
 * @package Seat\Services\Repositories\Character
 */
trait Killmails
{
    /**
     * Return the killmails for a character.
     *
     * @param \Illuminate\Support\Collection $character_id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Seat\Eveapi\Models\Killmails\CharacterKillmail
     */
    public function getCharacterKillmails(Collection $character_id)
    {

        return CharacterKillmail::with(
            'killmail_detail',
            'killmail_detail.solar_system',
            'killmail_victim',
            'killmail_victim.ship_type',
            'killmail_victim.victim_character',
            'killmail_victim.victim_corporation',
            'killmail_victim.victim_alliance')
            ->whereIn('character_killmails.character_id', $character_id->toArray());

    }
}
