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

use Seat\Eveapi\Models\Character\WalletJournal;
use Seat\Eveapi\Models\Character\WalletTransaction;

/**
 * Class Wallet.
 * @package Seat\Services\Repositories\Character
 */
trait Wallet
{
    /**
     * Retreive Wallet Journal Entries for a Character.
     *
     * @param int  $character_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return
     */
    public function getCharacterWalletJournal(
        int $character_id, bool $get = true, int $chunk = 50)
    {

        $journal = WalletJournal::leftJoin('eve_ref_types',
            'character_wallet_journals.refTypeID', '=',
            'eve_ref_types.refTypeID')
            ->where('characterID', $character_id);

        if ($get)
            return $journal->orderBy('date', 'desc')
                ->paginate($chunk);

        return $journal;
    }

    /**
     * Retreive Wallet Transaction Entries for a Character.
     *
     * @param int  $character_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return
     */
    public function getCharacterWalletTransactions(
        int $character_id, bool $get = true, int $chunk = 50)
    {

        $transactions = WalletTransaction::where('characterID', $character_id);

        if ($get)
            return $transactions->orderBy('transactionDateTime', 'desc')
                ->paginate($chunk);

        return $transactions;
    }
}
