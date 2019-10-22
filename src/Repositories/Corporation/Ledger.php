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

use Illuminate\Database\Eloquent\Builder;
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

        return $this->getCorporationLedgerPeriods($corporation_id)
            ->whereIn('ref_type', ['bounty_prizes', 'bounty_prize'])
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

        return $this->getCorporationLedgerPeriods($corporation_id)
            ->whereIn('ref_type', ['planetary_import_tax', 'planetary_export_tax'])
            ->get();
    }

    /**
     * @param int $corporation_id
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerOfficesRentalsPeriods(int $corporation_id): Collection
    {
        return $this->getCorporationLedgerPeriods($corporation_id)
            ->whereIn('ref_type', ['office_rental_fee'])
            ->get();
    }

    /**
     * @param int $corporation_id
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerIndustryFacilityPeriods(int $corporation_id): Collection
    {
        return $this->getCorporationLedgerPeriods($corporation_id)
            ->whereIn('ref_type', ['industry_job_tax'])
            ->get();
    }

    /**
     * @param int $corporation_id
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerReprocessingPeriods(int $corporation_id): Collection
    {
        return $this->getCorporationLedgerPeriods($corporation_id)
            ->whereIn('ref_type', ['reprocessing_tax'])
            ->get();
    }

    /**
     * @param int $corporation_id
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerJumpClonesPeriods(int $corporation_id): Collection
    {
        return $this->getCorporationLedgerPeriods($corporation_id)
            ->whereIn('ref_type', ['jump_clone_activation_fee', 'jump_clone_installation_fee'])
            ->get();
    }

    /**
     * @param int $corporation_id
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerJumpBridgesPeriods(int $corporation_id): Collection
    {
        return $this->getCorporationLedgerPeriods($corporation_id)
            ->whereIn('ref_type', ['structure_gate_jump'])
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

        $group_column = 'second_party_id';
        $ref_types = ['bounty_prizes', 'bounty_prize'];

        return $this->getCorporationLedgerByMonth($corporation_id, $group_column, $ref_types, $year, $month);
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
        $group_column = 'first_party_id';
        $ref_types = ['planetary_import_tax', 'planetary_export_tax'];

        return $this->getCorporationLedgerByMonth($corporation_id, $group_column, $ref_types, $year, $month);
    }

    /**
     * @param int $corporation_id
     * @param int|null $year
     * @param int|null $month
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerOfficesRentalsByMonth(int $corporation_id,
                                             ?int $year = null,
                                             ?int $month = null): Collection
    {
        $group_column = 'second_party_id';
        $ref_types = ['office_rental_fee'];

        return $this->getCorporationLedgerByMonth($corporation_id, $group_column, $ref_types, $year, $month);
    }

    /**
     * @param int $corporation_id
     * @param int|null $year
     * @param int|null $month
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerIndustryFacilityByMonth(int $corporation_id,
                                                  ?int $year = null,
                                                  ?int $month = null): Collection
    {
        $group_column = 'second_party_id';
        $ref_types = ['industry_job_tax'];

        return $this->getCorporationLedgerByMonth($corporation_id, $group_column, $ref_types, $year, $month);
    }

    /**
     * @param int $corporation_id
     * @param int|null $year
     * @param int|null $month
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerReprocessingByMonth(int $corporation_id,
                                                            ?int $year = null,
                                                            ?int $month = null): Collection
    {
        $group_column = 'first_party_id';
        $ref_types = ['reprocessing_tax'];

        return $this->getCorporationLedgerByMonth($corporation_id, $group_column, $ref_types, $year, $month);
    }

    /**
     * @param int $corporation_id
     * @param int|null $year
     * @param int|null $month
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerJumpClonesByMonth(int $corporation_id,
                                                          ?int $year = null,
                                                          ?int $month = null): Collection
    {
        $group_column = 'first_party_id';
        $ref_types = ['jump_clone_activation_fee', 'jump_clone_installation_fee'];

        return $this->getCorporationLedgerByMonth($corporation_id, $group_column, $ref_types, $year, $month);
    }

    /**
     * @param int $corporation_id
     * @param int|null $year
     * @param int|null $month
     * @return \Illuminate\Support\Collection
     * @author soratidus999
     */
    public function getCorporationLedgerJumpBridgesByMonth(int $corporation_id,
                                                        ?int $year = null,
                                                        ?int $month = null): Collection
    {
        $group_column = 'first_party_id';
        $ref_types = ['structure_gate_jump'];

        return $this->getCorporationLedgerByMonth($corporation_id, $group_column, $ref_types, $year, $month);
    }

    /**
     * @param int $corporation_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getCorporationLedgerPeriods(int $corporation_id): Builder
    {
        return CorporationWalletJournal::select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporation_id', $corporation_id)
            ->orderBy('date', 'desc');
    }

    /**
     * @param int $corporation_id
     * @param string $group_field
     * @param array $ref_types
     * @param int|null $year
     * @param int|null $month
     * @return \Illuminate\Support\Collection
     */
    private function getCorporationLedgerByMonth(int $corporation_id,
                                                 string $group_field,
                                                 array $ref_types,
                                                 ?int $year = null,
                                                 ?int $month = null): Collection
    {
        return CorporationWalletJournal::select(DB::raw('ROUND(SUM(amount)) as total'), $group_field)
            ->where('corporation_id', $corporation_id)
            ->whereIn('ref_type', $ref_types)
            ->whereYear('date', ! is_null($year) ? $year : date('Y'))
            ->whereMonth('date', ! is_null($month) ? $month : date('m'))
            ->groupBy($group_field)
            ->orderBy(DB::raw('SUM(amount)'), 'desc')
            ->get();
    }
}
