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

namespace Seat\Services\Repositories\Seat\Filters;

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class DataTablesFilter.
 * @package Seat\Services\Repositories\Seat\Filters
 */
trait NamedIdFilter
{
    /**
     * @param $keyword
     *
     * @return \Illuminate\Support\Collection
     */
    public function getIdsForNames($keyword) : Collection
    {
        return UniverseName::where('name', 'like', '%' . $keyword . '%')
            ->get()
            ->map(function ($resolved_id) {
                return $resolved_id->entity_id;
            })
            ->merge(CharacterInfo::where('name', 'like', '%' . $keyword . '%')
                ->get()
                ->map(function ($character_info) {
                    return $character_info->character_id;
                })
            );
    }
}
