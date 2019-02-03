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

namespace Seat\Services\Repositories\Corporation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;

/**
 * Class Ledger.
 * @package Seat\Services\Repositories\Corporation
 */
trait Ledger
{
    /**
     * Return the Bounty Prize Payout dates for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerBountyPrizeDates(int $corporation_id): Collection
    {

        return CorporationWalletJournal::select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporation_id', $corporation_id)
            ->whereIn('ref_type', ['bounty_prizes', 'bounty_prize'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Return the PI Payout dates for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerPIDates(int $corporation_id): Collection
    {

        // TODO : spawn native indexes on month and year inside corporation_wallet_journal table
        return CorporationWalletJournal::select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporation_id', $corporation_id)
            ->whereIn('ref_type', ['planetary_import_tax', 'planetary_export_tax'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get a Corporations Bounty Prizes for a specific year / month.
     *
     * @param int $corporation_id
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerBountyPrizeByMonth(int $corporation_id,
                                                           int $year = null,
                                                           int $month = null): Collection
    {

        return CorporationWalletJournal::select(
            DB::raw(
                'MONTH(date) as month, YEAR(date) as year, ' .
                'ROUND(SUM(amount)) as total, second_party_id'
            ))
            ->where('corporation_id', $corporation_id)
            ->whereIn('ref_type', ['bounty_prizes', 'bounty_prize'])
            ->where(DB::raw('YEAR(date)'), ! is_null($year) ? $year : date('Y'))
            ->where(DB::raw('MONTH(date)'), ! is_null($month) ? $month : date('m'))
            ->groupBy('second_party_id')
            ->orderBy(DB::raw('SUM(amount)'), 'desc')
            ->get();
    }

    /**
     * Get a Corporations PI Payouts for a specific year / month.
     *
     * @param int $corporation_id
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerPITotalsByMonth(int $corporation_id,
                                                        int $year = null,
                                                        int $month = null): Collection
    {

        // TODO : spawn native indexes on month and year inside corporation_wallet_journal table
        return CorporationWalletJournal::select(
            DB::raw(
                'MONTH(date) as month, YEAR(date) as year, ' .
                'ROUND(SUM(amount)) as total, first_party_id'
            ))
            ->where('corporation_id', $corporation_id)
            ->where(DB::raw('YEAR(date)'), ! is_null($year) ? $year : date('Y'))
            ->where(DB::raw('MONTH(date)'), ! is_null($month) ? $month : date('m'))
            ->whereIn('ref_type', ['planetary_import_tax', 'planetary_export_tax'])
            ->groupBy('first_party_id')
            ->orderBy(DB::raw('SUM(amount)'), 'desc')
            ->get();

    }
}
