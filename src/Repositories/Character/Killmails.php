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

namespace Seat\Services\Repositories\Character;

use Seat\Eveapi\Models\Character\KillMail;

/**
 * Class Killmails
 * @package Seat\Services\Repositories\Character
 */
trait Killmails
{

    /**
     * Return the killmails for a character
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

        $killmails = KillMail::select(
            '*',
            'character_kill_mails.characterID as ownerID',
            'kill_mail_details.characterID as victimID')
            ->leftJoin(
                'kill_mail_details',
                'character_kill_mails.killID', '=',
                'kill_mail_details.killID')
            ->leftJoin(
                'invTypes',
                'kill_mail_details.shipTypeID', '=',
                'invTypes.typeID')
            ->leftJoin('mapDenormalize',
                'kill_mail_details.solarSystemID', '=',
                'mapDenormalize.itemID')
            ->where('character_kill_mails.characterID', $character_id);

        if ($get)
            return $killmails->orderBy('character_kill_mails.killID', 'desc')
                ->paginate($chunk);

        return $killmails;

    }

}
