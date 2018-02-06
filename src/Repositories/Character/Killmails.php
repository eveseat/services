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

namespace Seat\Services\Repositories\Character;

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
     * @param int  $character_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return
     */
    public function getCharacterKillmails(
        int $character_id, bool $get = true, int $chunk = 200)
    {

        $killmails = CharacterKillmail::select(
            '*',
            'character_killmails.character_id as ownerID',
            'killmail_victims.character_id as victimID')
            ->leftJoin(
                'killmail_details',
                'character_killmails.killmail_id', '=',
                'killmail_details.killmail_id')
            ->leftJoin(
                'killmail_victims',
                'killmail_victims.killmail_id', '=',
                'character_killmails.killmail_id'
            )
            ->leftJoin(
                'invTypes',
                'killmail_victims.ship_type_id', '=',
                'invTypes.typeID')
            ->leftJoin('mapDenormalize',
                'kill_mail_details.solar_system_id', '=',
                'mapDenormalize.itemID')
            ->where('character_killmails.character_id', $character_id);

        if ($get)
            return $killmails->orderBy('character_killmails.killmail_id', 'desc')
                ->paginate($chunk);

        return $killmails;

    }
}
