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
use Seat\Eveapi\Models\Character\CharacterNotification;

/**
 * Class Notifications.
 * @package Seat\Services\Repositories\Character
 */
trait Notifications
{
    /**
     * Return notifications for a character.
     *
     * @param int $character_id
     * @param int $chunk
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterNotifications(int $character_id, int $chunk = 50): Collection
    {

        return CharacterNotification::where('character_id', $character_id)
            ->take($chunk)
            ->orderBy('timestamp', 'desc')
            ->get();
    }
}
