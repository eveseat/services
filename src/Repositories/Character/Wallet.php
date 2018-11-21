<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;

/**
 * Class Wallet.
 * @package Seat\Services\Repositories\Character
 */
trait Wallet
{
    /**
     * Query the eveseat/resources repository for SDE
     * related information.
     *
     * @param \Illuminate\Support\Collection $character_ids
     *
     * @return mixed
     */
    public function getCharacterWalletJournal(Collection $character_ids)
    {

        return $journal = CharacterWalletJournal::with('first_party','second_party')
            ->whereIn('character_id', $character_ids->toArray());
    }

    /**
     * Retrieve Wallet Transaction Entries for a Character.
     *
     * @param int  $character_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return mixed
     */
    public function getCharacterWalletTransactions(
        int $character_id, bool $get = true, int $chunk = 50)
    {

        $transactions = CharacterWalletTransaction::where('character_id', $character_id);

        if ($get)
            return $transactions->orderBy('date', 'desc')
                ->paginate($chunk);

        return $transactions;
    }
}
