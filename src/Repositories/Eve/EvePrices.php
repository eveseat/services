<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 12.09.2018
 * Time: 20:06
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