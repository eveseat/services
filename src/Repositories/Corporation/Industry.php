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
use Seat\Eveapi\Models\PlanetaryInteraction\CorporationCustomsOffice;

/**
 * Class Industry.
 * @package Seat\Services\Repositories\Corporation
 */
trait Industry
{
    /**
     * Return the Industry jobs for a Corporation.
     *
     * @param int  $corporation_id
     * @param bool $get
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationIndustry(int $corporation_id, bool $get = true)
    {

        $industry = DB::table('corporation_industry_jobs as a')
            ->select(DB::raw('
                a.*,
                ramActivities.*,
                blueprintType.typeName as blueprintTypeName,
                productType.typeName as productTypeName,

                --
                -- Start Facility Name Lookup
                --
                CASE
                when a.location_id BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.location_id-6000000)
                when a.location_id BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.location_id-6000001)
                when a.location_id BETWEEN 66014934 AND 67999999 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.location_id-6000000)
                when a.location_id BETWEEN 60014861 AND 60014928 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.location_id)
                when a.location_id BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.location_id)
                when a.location_id >= 61000000 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.location_id)
                else (SELECT m.itemName FROM mapDenormalize AS m
                WHERE m.itemID = a.location_id) end
                AS facilityName'))
            ->leftJoin(
                'ramActivities',
                'ramActivities.activityID', '=',
                'a.activity_id')// corporation_industry_jobs aliased to a
            ->join(
                'invTypes as blueprintType',
                'blueprintType.typeID', '=',
                'a.blueprint_type_id'
            )
            ->join(
                'invTypes as productType',
                'productType.typeID', '=',
                'a.product_type_id'
            )
            ->where('a.corporation_id', $corporation_id);

        if ($get)
            return $industry
                ->orderBy('end_date', 'desc')
                ->get();

        return $industry;

    }

    /**
     * Return a Corporations Customs Offices.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationCustomsOffices(int $corporation_id): Collection
    {

        return CorporationCustomsOffice::where('corporation_id', $corporation_id)
            ->get();
    }
}
