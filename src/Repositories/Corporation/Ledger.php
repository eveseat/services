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
use Illuminate\Support\Facades\DB;

/**
 * Class Ledger.
 * @package Seat\Services\Repositories\Corporation
 */
trait Ledger
{
    /**
     * Return the Bountry Prize Payout dates for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerBountyPrizeDates(int $corporation_id): Collection
    {

        return DB::table('corporation_wallet_journals')
            ->select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporationID', $corporation_id)
            ->where('refTypeID', '85')
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

        return DB::table('corporation_wallet_journals')
            ->select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporationID', $corporation_id)
            ->where(function ($query) {

                $query->where('refTypeID', 96)
                    ->orWhere('refTypeID', 97);
            })
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Return the Mission Tax dates for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerMissionDates(int $corporation_id): Collection
    {

        return DB::table('corporation_wallet_journals')
            ->select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporationID', $corporation_id)
            ->where(function ($query) {

                $query->where('refTypeID', 33)
                    ->orWhere('refTypeID', 34);
            })
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Return the Incursion Tax dates for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerIncursionDates(int $corporation_id): Collection
    {

        return DB::table('corporation_wallet_journals')
            ->select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporationID', $corporation_id)
            ->where('ownerName1', "CONCORD")
            ->where('refTypeID', 99)
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

        return $this->getCorporationLedgerTotalsByMonth($corporation_id, $year, $month, 
            2,  array('85'), array());

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

        return $this->getCorporationLedgerTotalsByMonth($corporation_id, $year, $month, 
            1,  array('96', '97'), array());

    }

    /**
     * Get a Corporations Mission Taxes for a specific year / month.
     *
     * @param int $corporation_id
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerMissionTotalsByMonth(int $corporation_id,
                                                        int $year = null,
                                                        int $month = null): Collection
    {

        return $this->getCorporationLedgerTotalsByMonth($corporation_id, $year, $month, 
            2,  array('33', '34'), array());

    }

    /**
     * Get a Corporations Incursion Taxes for a specific year / month.
     *
     * @param int $corporation_id
     * @param int $year
     * @param int $month
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerIncursionTotalsByMonth(int $corporation_id,
                                                        int $year = null,
                                                        int $month = null): Collection
    {

        return $this->getCorporationLedgerTotalsByMonth($corporation_id, $year, $month, 
            2,  array('99'), array('ownerName1' => 'CONCORD'));

    }

    /**
     * Get a Corporations Incursion Taxes for a specific year / month.
     *
     * @param int $corporation_id
     * @param int $year
     * @param int $month
     * @param int $oid
     * @param array $refTypes
     * @param array $addWhere
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationLedgerTotalsByMonth(int $corporation_id,
                                                        int $year = null,
                                                        int $month = null,
                                                        int $oid = 2,
                                                        array $ref_types,
							array $add_where): Collection
    {

        $select = 'MONTH(date) as month, YEAR(date) as year, ROUND(SUM(amount)) as total, ';
	$select .= 'ownerName' . $oid . ', ownerID' . $oid;

	$query = DB::table('corporation_wallet_journals')
            ->select(
                DB::raw(
                    $select 
                ))
            ->where('corporationID', $corporation_id)
            ->where(DB::raw('YEAR(date)'), ! is_null($year) ? $year : date('Y'))
            ->where(DB::raw('MONTH(date)'), ! is_null($month) ? $month : date('m'));

            if (count($add_where) > 0) {
                foreach ($add_where as $key => $value) {
	                $query->where($key, $value);
	        }
            }
            if (count($ref_types) > 0) {
                $query->where(function ($query) use ($ref_types) {
                    $query->where('refTypeID', array_shift($ref_types));
                    foreach ($ref_types as $value) { 
                        $query->orwhere('refTypeID', $value);
                    }
                });
            }

        $query->groupBy('ownerName'.$oid)
        ->orderBy(DB::raw('SUM(amount)'), 'desc');

        return $query->get();

    }
}
