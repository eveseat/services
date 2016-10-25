<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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

namespace Seat\Services\Repositories\Corporation;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Corporation\CorporationSheetWalletDivision;
use Seat\Eveapi\Models\Corporation\WalletJournal;
use Seat\Eveapi\Models\Corporation\WalletTransaction;
use Seat\Services\Helpers\Filterable;

/**
 * Class Wallet
 * @package Seat\Services\Repositories\Corporation
 */
trait Wallet
{

    use Filterable;

    /**
     * Return the Corporation Wallet Divisions for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationWalletDivisions(int $corporation_id) : Collection
    {

        return CorporationSheetWalletDivision::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return the Wallet Division Summary for a Corporation
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationWalletDivisionSummary(int $corporation_id) : Collection
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
     * Return a Wallet Journal for a Corporation
     *
     * @param int                      $corporation_id
     * @param int                      $chunk
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCorporationWalletJournal(
        int $corporation_id, int $chunk = 50, Request $request = null) : LengthAwarePaginator
    {

        $journal = WalletJournal::leftJoin('eve_ref_types',
            'corporation_wallet_journals.refTypeID', '=',
            'eve_ref_types.refTypeID')
            ->where('corporationID', $corporation_id);

        // Apply any received filters
        if ($request && $request->filter)
            $journal = $this->where_filter(
                $journal, $request->filter, config('web.filter.rules.corporation_journal'));

        return $journal->orderBy('date', 'desc')
            ->paginate($chunk);

    }

    /**
     * Return Wallet Transactions for a Corporation
     *
     * @param int                      $corporation_id
     * @param int                      $chunk
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCorporationWalletTransactions(
        int $corporation_id, int $chunk = 50, Request $request = null) : LengthAwarePaginator
    {

        $transactions = WalletTransaction::where('corporationID', $corporation_id);

        // Apply any received filters
        if ($request && $request->filter)
            $transactions = $this->where_filter(
                $transactions, $request->filter, config('web.filter.rules.corporation_transactions'));

        return $transactions->orderBy('transactionDateTime', 'desc')
            ->paginate($chunk);
    }

}
