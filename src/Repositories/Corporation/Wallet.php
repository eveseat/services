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

namespace Seat\Services\Repositories\Corporation;

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Corporation\CorporationDivision;
use Seat\Eveapi\Models\Corporation\WalletJournal;
use Seat\Eveapi\Models\Corporation\WalletTransaction;

/**
 * Class Wallet.
 * @package Seat\Services\Repositories\Corporation
 */
trait Wallet
{
    /**
     * Return the Corporation Wallet Divisions for a Corporation.
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationWalletDivisions(int $corporation_id): Collection
    {

        return CorporationDivision::where('corporation_id', $corporation_id)
            ->where('type', 'wallet')
            ->orderBy('division')
            ->get();
    }

    /**
     * Return the Wallet Division Summary for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationWalletDivisionSummary(int $corporation_id): Collection
    {

        return CorporationSheetWalletDivision::join(
            'corporation_account_balances',
            'corporation_sheet_wallet_divisions.accountKey', '=',
            'corporation_account_balances.accountKey')
            ->select(
                'corporation_account_balances.balance',
                'corporation_sheet_wallet_divisions.description')
            ->where('corporation_account_balances.corporationID', $corporation_id)
            ->where('corporation_sheet_wallet_divisions.corporationID', $corporation_id)
            ->get();

    }

    /**
     * Return a Wallet Journal for a Corporation.
     *
     * @param int  $corporation_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCorporationWalletJournal(
        int $corporation_id, bool $get = true, int $chunk = 50)
    {

        $journal = WalletJournal::leftJoin(
            'eve_ref_types', function ($join) {

            $join->on('corporation_wallet_journals.refTypeID', '=',
                'eve_ref_types.refTypeID');
        })
            ->where('corporationID', $corporation_id);

        if ($get)
            return $journal->orderBy('date', 'desc')
                ->paginate($chunk);

        return $journal;

    }

    /**
     * Return Wallet Transactions for a Corporation.
     *
     * @param int  $corporation_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return
     */
    public function getCorporationWalletTransactions(
        int $corporation_id, bool $get = true, int $chunk = 50)
    {

        $transactions = WalletTransaction::where('corporationID', $corporation_id);

        if ($get)
            return $transactions->orderBy('transactionDateTime', 'desc')
                ->paginate($chunk);

        return $transactions;
    }
}
