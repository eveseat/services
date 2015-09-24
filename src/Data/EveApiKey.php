<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

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

namespace Seat\Services\Data;

use Seat\Eveapi\Models\EveApiKey as EveApiKeyModel;

/**
 * Class EveApiKey
 * @package Seat\Services\Data
 */
trait EveApiKey
{

    /**
     * Return an array with character information.
     * This includes the key info as well as the
     * extended information such as type/expiry etc.
     *
     * @return array
     */
    public function all_with_info()
    {

        $response = [];

        foreach (EveApiKeyModel::all() as $key) {

            $response[$key->key_id] = [
                'enabled'     => $key->enabled,
                'user_id'     => $key->user_id,
                'key_id'      => $key->key_id,
                'v_code'      => str_limit($key->v_code, 15),
                'access_mask' => $key->info ? $key->info->accessMask : null,
                'type'        => $key->info ? $key->info->type : null,
                'expires'     => $key->info ? $key->info->expires : null,
                'last_error'  => $key->last_error,
                'characters'  => count($key->characters) > 0 ?
                    implode(', ', $key->characters->lists('characterName')->all()) : null
            ];

        }

        return $response;
    }
}