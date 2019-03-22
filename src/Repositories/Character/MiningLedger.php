<?php

/**
 * MIT License.
 *
 * Copyright (c) 2019. Felix Huber
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Seat\Services\Repositories\Character;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Industry\CharacterMining;

/**
 * Trait MiningLedger.
 *
 * @package Seat\Services\Repositories\Character
 */
trait MiningLedger
{
    /**
     * @param int  $character_id
     * @param bool $get
     *
     * @return mixed
     */
    public function getCharacterLedger(Collection $character_ids) : Builder
    {

        return CharacterMining::with('type', 'system')
            ->select(
                'character_minings.date',
                'character_minings.character_id',
                'solar_system_id',
                'character_minings.type_id',
                'historical_prices.average_price')
            ->leftJoin('historical_prices', function ($join) {
                $join->on('historical_prices.type_id', '=', 'character_minings.type_id')
                     ->on('historical_prices.date', '=', 'character_minings.date');
            })
            ->whereIn('character_id', $character_ids->toArray());
    }
}
