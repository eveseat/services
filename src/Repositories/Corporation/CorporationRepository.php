<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

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

use DB;
use Illuminate\Http\Request;
use Seat\Eveapi\Models\Corporation\ContactList;
use Seat\Eveapi\Models\Corporation\ContactListLabel;
use Seat\Eveapi\Models\Corporation\CorporationSheet;
use Seat\Eveapi\Models\Corporation\CorporationSheetDivision;
use Seat\Eveapi\Models\Corporation\CorporationSheetWalletDivision;
use Seat\Eveapi\Models\Corporation\KillMail;
use Seat\Eveapi\Models\Corporation\Standing;
use Seat\Eveapi\Models\Corporation\WalletJournal;
use Seat\Services\Helpers\Filterable;

/**
 * Class CorporationRepository
 * @package Seat\Services\Repositories\Corporation
 */
trait CorporationRepository
{

    use Filterable;

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCorporations()
    {

        return CorporationSheet::all();
    }

    /**
     * Return the corporations for which a user has access.
     *
     * @return mixed
     */
    public function getAllCorporationsWithAffiliationsAndFilters()
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        // Start a fresh query
        $corporations = new CorporationSheet;

        // Check if this user us a superuser. If not,
        // limit to stuff only they can see.
        if (!$user->hasSuperUser()) {

            // Add affiliated corporations based on the
            // corporation.list_all permission
            if ($user->has('corporation.list_all', false))
                $corporations = $corporations->orWhereIn(
                    'corporationID', array_keys($user->getAffiliationMap()['corp']));

            // Add any keys the user may own. This is a slightly
            // complex case as we need to sub select a few things
            $corporations = $corporations->orWhereIn('corporationID',

                // The return array of all of the below is a
                // nested mess. We can just flatten it.
                array_flatten($user->keys()
                    // Include info.characters so that we can
                    // filter it down in the map() function
                    // below.
                    ->with('info.characters')
                    // Info itself has a constraint applied, checking
                    // if the api key type is that of a corp.
                    ->whereHas('info', function ($query) {

                        $query->where('type', 'Corporation');

                    })->get()->map(function ($item) {

                        // We finally map the resultant Collection
                        // object and list the corporationID out of
                        // the $key->info->characters relation.
                        return $item->info->characters
                            ->lists('corporationID')->toArray();
                    })));
        }

        return $corporations->orderBy('corporationName', 'desc')
            ->get();

    }

    /**
     * Return the assets list for a Corporation
     *
     * @param $corporation_id
     *
     * @return array|static[]
     */
    public function getCorporationAssets($corporation_id)
    {

        return DB::table('corporation_asset_lists as a')
            ->select(DB::raw("
                --
                -- Select All Fields
                --
                *,

                --
                -- Start the Lookation Lookup
                --
                CASE
                when a.locationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.locationID-6000000)
                when a.locationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.locationID-6000001)
                when a.locationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.locationID-6000000)
                when a.locationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.locationID)
                when a.locationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.locationID)
                when a.locationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.locationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.locationID) end
                    AS location"))
            ->join('invTypes',
                'a.typeID', '=',
                'invTypes.typeID')
            ->join('invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->where('a.corporationID', $corporation_id)
            ->get();

    }

    /**
     * Return the contacts list for a corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationContacts($corporation_id)
    {

        return ContactList::where('corporationID', $corporation_id)
            ->orderBy('standing', 'desc')
            ->get();

    }

    /**
     * Return the contracts for a Corporation
     *
     * @param $corporation_id
     *
     * @return array|static[]
     */
    public function getCorporationContracts($corporation_id)
    {

        return DB::table(DB::raw('corporation_contracts as a'))
            ->select(DB::raw(
                "
                --
                -- All Columns
                --
                *,

                --
                -- Start Location Lookup
                --
                CASE
                when a.startStationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.startStationID-6000000)
                when a.startStationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.startStationID-6000001)
                when a.startStationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.startStationID-6000000)
                when a.startStationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.startStationID)
                when a.startStationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.startStationID)
                when a.startStationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.startStationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.startStationID) end
                AS startlocation,

                --
                -- End Location Lookup
                --
                CASE
                when a.endstationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.endStationID-6000000)
                when a.endStationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.endStationID-6000001)
                when a.endStationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.endStationID-6000000)
                when a.endStationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.endStationID)
                when a.endStationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.endStationID)
                when a.endStationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.endStationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.endStationID) end
                AS endlocation "))
            ->where('a.corporationID', $corporation_id)
            ->orderBy('dateIssued', 'desc')
            ->get();
    }

    /**
     * Return the contact labels for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationContactsLabels($corporation_id)
    {

        return ContactListLabel::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return the Corporation Divisions for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationDivisions($corporation_id)
    {

        return CorporationSheetDivision::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return the Industry jobs for a Corporation
     *
     * @param $corporation_id
     *
     * @return array|static[]
     */
    public function getCorporationIndustry($corporation_id)
    {

        return DB::table('corporation_industry_jobs as a')
            ->select(DB::raw("
                *,

                --
                -- Start Facility Name Lookup
                --
                CASE
                when a.stationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID-6000000)
                when a.stationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID-6000001)
                when a.stationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID-6000000)
                when a.stationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID)
                when a.stationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID)
                when a.stationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                WHERE m.itemID = a.stationID) end
                AS facilityName"))
            ->leftJoin(
                'ramActivities',
                'ramActivities.activityID', '=',
                'a.activityID')// corporation_industry_jobs aliased to a
            ->where('a.corporationID', $corporation_id)
            ->orderBy('endDate', 'desc')
            ->get();
    }

    /**
     * Return the Killmails for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationKillmails($corporation_id)
    {

        return KillMail::select(
            '*',
            'corporation_kill_mails.corporationID as ownerID',
            'kill_mail_details.corporationID as victimID')
            ->leftJoin(
                'kill_mail_details',
                'corporation_kill_mails.killID', '=',
                'kill_mail_details.killID')
            ->leftJoin(
                'invTypes',
                'kill_mail_details.shipTypeID', '=',
                'invTypes.typeID')
            ->leftJoin('mapDenormalize',
                'kill_mail_details.solarSystemID', '=',
                'mapDenormalize.itemID')
            ->where('corporation_kill_mails.corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return the Market Orders for a Corporation
     *
     * @param $corporation_id
     *
     * @return array|static[]
     */
    public function getCorporationMarketOrders($corporation_id)
    {

        return DB::table(DB::raw('corporation_market_orders as a'))
            ->select(DB::raw(
                "
                --
                -- Select All
                --
                *,

                --
                -- Start stationName Lookup
                --
                CASE
                when a.stationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID-6000000)
                when a.stationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID-6000001)
                when a.stationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID-6000000)
                when a.stationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID)
                when a.stationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID)
                when a.stationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.stationID) end
                    AS stationName"))
            ->join(
                'invTypes',
                'a.typeID', '=',
                'invTypes.typeID')
            ->join(
                'invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->where('a.corporationID', $corporation_id)
            ->orderBy('a.issued', 'desc')
            ->get();
    }

    /**
     * Return the Corporation Sheet for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationSheet($corporation_id)
    {

        return CorporationSheet::where('corporationID', $corporation_id)
            ->first();
    }

    /**
     * Return the standings for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationStandings($corporation_id)
    {

        return Standing::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return the Corporation Wallet Divisions for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationWalletDivisions($corporation_id)
    {

        return CorporationSheetWalletDivision::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return a Wallet Journal for a Corporation
     *
     * @param                               $corporation_id
     * @param int                           $chunk
     * @param \Illuminate\Http\Request|null $request
     *
     * @return mixed
     * @throws \Seat\Services\Exceptions\FilterException
     */
    public function getCorporationWalletJournal($corporation_id, $chunk = 50, Request $request = null)
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
            ->take($chunk)
            ->get();

    }

}
