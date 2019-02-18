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

namespace Seat\Services\Repositories\Eve;

use Seat\Eveapi\Models\Market\Price;
use Seat\Services\Models\HistoricalPrices;

trait EvePrices
{
    /**
     * @param int $type_id
     * @param string f.e. "2018-09-13" $date
     *
     * @return mixed
     */
    public function getHistoricalPrice(int $type_id, string $date)
    {

        // If market_prices table is not populated update all entries with both average and adjusted price equals 0.0
        HistoricalPrices::where('average_price', 0.0)->where('adjusted_price', 0.0)
            ->update([
                'average_price'  => is_null(optional(Price::find($type_id))->average_price) ? 0.0 : Price::find($type_id)->average_price,
                'adjusted_price' => is_null(optional(Price::find($type_id))->adjusted_price) ? 0.0 : Price::find($type_id)->adjusted_price,
            ]);

        return HistoricalPrices::firstOrCreate([
            'type_id' => $type_id,
            'date'    => carbon($date)->setTimezone('UTC')->toDateString(),
        ], [
            'date'           => carbon($date)->setTimezone('UTC')->toDateString(),
            'average_price'  => is_null(optional(Price::find($type_id))->average_price) ? 0.0 : Price::find($type_id)->average_price,
            'adjusted_price' => is_null(optional(Price::find($type_id))->adjusted_price) ? 0.0 : Price::find($type_id)->adjusted_price,
        ]);

    }
}
