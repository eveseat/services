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

namespace Seat\Services\Repositories\Corporation;

use Seat\Eveapi\Models\Industry\CorporationIndustryMiningExtraction;

/**
 * Trait Extractions.
 * @package Seat\Services\Repositories\Corporation
 */
trait Extractions
{
    /**
     * @param int $corporation_id
     * @return \Illuminate\Database\Eloquent\Builder|CorporationIndustryMiningExtraction
     */
    public function getCorporationExtractions(int $corporation_id)
    {
        // retrieve any valid extraction for the current corporation
        return CorporationIndustryMiningExtraction::with(
            'moon', 'moon.system', 'moon.constellation', 'moon.region', 'moon.moon_content',
            'structure', 'structure.info', 'structure.services')
            ->where('corporation_id', $corporation_id)
            ->where('natural_decay_time', '>', carbon()->subSeconds(CorporationIndustryMiningExtraction::THEORETICAL_DEPLETION_COUNTDOWN))
            ->orderBy('chunk_arrival_time');
    }
}
