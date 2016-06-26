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

use DB;
use Illuminate\Http\Request;
use Seat\Eveapi\Models\Corporation\AssetListContents;
use Seat\Eveapi\Models\Corporation\Bookmark;
use Seat\Eveapi\Models\Corporation\ContactList;
use Seat\Eveapi\Models\Corporation\ContactListLabel;
use Seat\Eveapi\Models\Corporation\CorporationSheet;
use Seat\Eveapi\Models\Corporation\CorporationSheetDivision;
use Seat\Eveapi\Models\Corporation\CorporationSheetWalletDivision;
use Seat\Eveapi\Models\Corporation\CustomsOffice;
use Seat\Eveapi\Models\Corporation\KillMail;
use Seat\Eveapi\Models\Corporation\Locations;
use Seat\Eveapi\Models\Corporation\MemberSecurity;
use Seat\Eveapi\Models\Corporation\MemberSecurityLog;
use Seat\Eveapi\Models\Corporation\MemberSecurityTitle;
use Seat\Eveapi\Models\Corporation\MemberTracking;
use Seat\Eveapi\Models\Corporation\Standing;
use Seat\Eveapi\Models\Corporation\Starbase;
use Seat\Eveapi\Models\Corporation\WalletJournal;
use Seat\Eveapi\Models\Corporation\WalletTransaction;
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
     * Returns a corporation assets grouped by location.
     * Only assets in space will appear here as assets
     * that are in stations dont have 'locations' entries.
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationAssetByLocation($corporation_id)
    {

        return Locations::leftJoin('corporation_asset_lists',
            'corporation_locations.itemID', '=',
            'corporation_asset_lists.itemID')
            ->leftJoin(
                'invTypes',
                'corporation_asset_lists.typeID', '=',
                'invTypes.typeID')
            ->where('corporation_locations.corporationID', $corporation_id)
            ->get()
            ->groupBy('mapID'); // <--- :O That is so sexy <3
    }

    /**
     * Return an assets contents. If no parent asset / item ids
     * are specified, then all assets for the corporation is
     * returned
     *
     * @param      $corporation_id
     * @param null $parent_asset_id
     * @param null $parent_item_id
     *
     * @return mixed
     */
    public function getCorporationAssetContents($corporation_id,
                                                $parent_asset_id = null,
                                                $parent_item_id = null)
    {

        $contents = AssetListContents::join('invTypes',
            'corporation_asset_list_contents.typeID', '=',
            'invTypes.typeID')
            ->where('corporationID', $corporation_id);

        if (!is_null($parent_asset_id))
            $contents = $contents->where('parentAssetItemID', $parent_asset_id);

        if (!is_null($parent_item_id))
            $contents = $contents->where('parentItemID', $parent_item_id);

        // TODO: Allow the nested lookups to occur.
        $contents = $contents->where('parentItemID', null);

        return collect($contents->get());
    }

    /**
     * Get a corporations Bookmarks
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationBookmarks($corporation_id)
    {

        return Bookmark::where('corporationID', $corporation_id)
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
     * Get the Member Security for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationMemberSecurity($corporation_id)
    {

        return MemberSecurity::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Get the security change logs for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationMemberSecurityLogs($corporation_id)
    {

        return MemberSecurityLog::where('corporationID', $corporation_id)
            ->orderBy('changeTime', 'desc')
            ->get();
    }

    /**
     * Get the titles for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationMemberSecurityTitles($corporation_id)
    {

        return MemberSecurityTitle::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return the Member Tracking for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationMemberTracking($corporation_id)
    {

        return MemberTracking::select(
            'corporation_member_trackings.*')
            ->selectSub(function ($query) {

                // Get the key status for the character
                return $query->from('eve_api_keys')
                    ->select('enabled')
                    ->join(
                        'account_api_key_infos',
                        'eve_api_keys.key_id', '=',
                        'account_api_key_infos.keyID')
                    ->join(
                        'account_api_key_info_characters',
                        'eve_api_keys.key_id', '=',
                        'account_api_key_info_characters.keyID')
                    ->where('account_api_key_infos.type', '!=', 'Corporation')
                    ->where('account_api_key_info_characters.characterID',
                        $query->raw('corporation_member_trackings.characterID'))
                    ->groupBy('corporation_member_trackings.characterID');

            }, 'enabled')
            ->leftJoin(
                'account_api_key_info_characters',
                'corporation_member_trackings.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->leftJoin(
                'eve_api_keys',
                'account_api_key_info_characters.keyID', '=',
                'eve_api_keys.key_id')
            ->where('corporation_member_trackings.corporationID',
                $corporation_id)
            ->groupBy('corporation_member_trackings.characterID')
            ->orderBy('name')
            ->get();
    }

    /**
     * Return a Corporations Customs Offices
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationCustomsOffices($corporation_id)
    {

        return CustomsOffice::select(
            'corporation_customs_offices.*',
            'corporation_customs_office_locations.itemName as planetName',
            'mapDenormalize.typeID AS planetTypeID',
            'invTypes.typeName AS planetTypeName')
            ->join(
                'corporation_customs_office_locations',
                'corporation_customs_offices.itemID', '=',
                'corporation_customs_office_locations.itemID')
            ->join(
                'mapDenormalize',
                'corporation_customs_office_locations.mapID', '=',
                'mapDenormalize.itemID')
            ->join(
                'invTypes',
                'invTypes.typeID', '=',
                'mapDenormalize.typeID')
            ->where('corporation_customs_offices.corporationID', $corporation_id)
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
     * Return a list of starbases for a Corporation. If
     * a starbaseID is provided, then only data for that
     * starbase is returned.
     *
     * @param      $corporation_id
     * @param null $starbase_id
     *
     * @return mixed
     */
    public function getCorporationStarbases($corporation_id, $starbase_id = null)
    {

        $starbase = Starbase::select(
            'corporation_starbases.itemID',
            'corporation_starbases.moonID',
            'corporation_starbases.state',
            'corporation_starbases.stateTimeStamp',
            'corporation_starbases.onlineTimeStamp',
            'corporation_starbases.onlineTimeStamp',
            'corporation_starbase_details.useStandingsFrom',
            'corporation_starbase_details.onAggression',
            'corporation_starbase_details.onCorporationWar',
            'corporation_starbase_details.allowCorporationMembers',
            'corporation_starbase_details.allowAllianceMembers',
            'corporation_starbase_details.fuelBlocks',
            'corporation_starbase_details.strontium',
            'corporation_starbase_details.starbaseCharter',
            'invTypes.typeID as starbaseTypeID',
            'invTypes.typeName as starbaseTypeName',
            'mapDenormalize.itemName as mapName',
            'mapDenormalize.security as mapSecurity',
            'invNames.itemName as moonName',
            'map_sovereignties.solarSystemName',
            'corporation_starbase_details.updated_at')
            ->selectSub(function ($query) {

                return $query->from('invControlTowerResources')
                    ->select('quantity')
                    // from invControlTowerResources, we can see
                    // that fuelBlock resourceTypeID's are around
                    // 4051 -> 4312. For that reason, we can just
                    // approximate the range that fuel blocks will
                    // fall in.
                    ->whereBetween('resourceTypeID', [4000, 5000])
                    ->where('purpose', 1)
                    ->where('controlTowerTypeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'baseFuelUsage')
            ->selectSub(function ($query) {

                return $query->from('invControlTowerResources')
                    ->select('quantity')
                    ->where('resourceTypeID', '=', 16275)
                    ->where('purpose', 4)
                    ->where('controlTowerTypeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'baseStrontUsage')
            ->selectSub(function ($query) {

                return $query->from('invTypes')
                    ->select('capacity')
                    ->where('groupID', 365)
                    ->where('typeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'fuelBaySize')
            ->selectSub(function ($query) {

                return $query->from('dgmTypeAttributes')
                    ->select('valueFloat')
                    ->where('dgmTypeAttributes.attributeID', 1233)
                    ->where('typeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'strontBaySize')
            ->selectSub(function ($query) {

                return $query->from('corporation_locations')
                    ->select('itemName')
                    ->where('itemID',
                        $query->raw('corporation_starbases.itemID'));

            }, 'starbaseName')
            ->selectSub(function ($query) use ($corporation_id) {

                return $query->from('map_sovereignties')
                    ->selectRaw(
                        'IF(solarSystemID, TRUE, FALSE) inSovSystem')
                    ->where('factionID', 0)
                    ->whereIn('allianceID', function ($subquery) use ($corporation_id) {

                        $subquery->from('corporation_sheets')
                            ->select('allianceID')
                            ->where('corporationID', $corporation_id);
                    })
                    ->where('solarSystemID',
                        $query->raw('corporation_starbases.locationID'));

            }, 'inSovSystem')
            ->selectSub(function ($query) {

                return $query->from('dgmTypeAttributes')
                    ->select('valueFloat')
                    // From dgmAttributeTypes,
                    // 757 = controlTowerSiloCapacityBonus
                    ->where('attributeID', 757)
                    ->where('typeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'siloCapacityBonus')
            ->join(
                'corporation_starbase_details',
                'corporation_starbases.itemID', '=',
                'corporation_starbase_details.itemID')
            ->join(
                'mapDenormalize',
                'corporation_starbases.locationID', '=',
                'mapDenormalize.itemID')
            ->join(
                'invNames',
                'corporation_starbases.moonID', '=',
                'invNames.itemID')
            ->join(
                'invTypes',
                'corporation_starbases.typeID', '=',
                'invTypes.typeID')
            ->leftJoin(
                'map_sovereignties',
                'corporation_starbases.locationID', '=',
                'map_sovereignties.solarSystemID')
            ->where('corporation_starbases.corporationID', $corporation_id)
            ->orderBy('invNames.itemName', 'asc');

        // If we did get a specific starbase_id to query then
        // just return what we have now for all of the starbases.
        if (is_null($starbase_id))
            return $starbase->get();

        // ... otherwise, filter down to the specific requested starbase
        // and grab some extra information about the silos etc at this tower.
        $starbase = $starbase->where('corporation_starbases.itemID', $starbase_id)
            ->first();

        // When calculating *actual* silo capacity, we need
        // to keep in mind that certain towers have bonusses
        // to silo cargo capacity, like amarr & gallente
        // towers do now. To calculate this, we will get the
        // siloCapacityBonus value from the starbase and add the
        // % capacity to actual modules that benefit from
        // the bonusses.
        $cargo_types_with_bonus = [14343, 17982]; // Silo, Coupling Array
        $assetlist_locations = $this->getCorporationAssetByLocation($corporation_id);
        $module_contents = $this->getCorporationAssetContents($corporation_id);

        // Check if we know of *any* assets at the moon where this tower is.
        if ($assetlist_locations->has($starbase->moonID)) {

            // Set the 'modules' key for the starbase
            $starbase->modules = $assetlist_locations->get($starbase->moonID)
                ->map(function ($asset) use (
                    $starbase,
                    $cargo_types_with_bonus,
                    $module_contents
                ) {

                    // Return a collection with module related info.
                    return [
                        'detail'           => $asset,
                        'used_volume'      => $module_contents->where(
                            'parentAssetItemID', $asset->itemID)->sum(function ($_) {

                            return $_->quantity * $_->volume;
                        }),
                        'available_volume' => in_array($asset->typeID, $cargo_types_with_bonus) ?
                            $asset->capacity * (1 + $starbase->siloCapacityBonus / 100) :
                            $asset->capacity,
                        'total_items'      => $module_contents->where(
                            'parentAssetItemID', $asset->itemID)->sum('quantity')
                    ];
                });
        }

        return $starbase;
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
     * Return the Wallet Division Summary for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationWalletDivisionSummary($corporation_id)
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
            ->paginate($chunk);

    }

    /**
     * Return Wallet Transactions for a Corporation
     *
     * @param                               $corporation_id
     * @param int                           $chunk
     * @param \Illuminate\Http\Request|null $request
     *
     * @return mixed
     * @throws \Seat\Services\Exceptions\FilterException
     */
    public function getCorporationWalletTransactions($corporation_id, $chunk = 50, Request $request = null)
    {

        $transactions = WalletTransaction::where('corporationID', $corporation_id);

        // Apply any received filters
        if ($request && $request->filter)
            $transactions = $this->where_filter(
                $transactions, $request->filter, config('web.filter.rules.corporation_transactions'));

        return $transactions->orderBy('transactionDateTime', 'desc')
            ->paginate($chunk);
    }

    /**
     * Return the Bountry Prize Payout dates for a Corporation
     *
     * @param $corporation_id
     *
     * @return array|static[]
     */
    public function getCorporationLedgerBountyPrizeDates($corporation_id)
    {

        return DB::table('corporation_wallet_journals')
            ->select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporationID', $corporation_id)
            ->where('refTypeID', '85')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Return the PI Payout dates for a Corporation
     *
     * @param $corporation_id
     *
     * @return array|static[]
     */
    public function getCorporationLedgerPIDates($corporation_id)
    {

        return DB::table('corporation_wallet_journals')
            ->select(DB::raw('DISTINCT MONTH(date) as month, YEAR(date) as year'))
            ->where('corporationID', $corporation_id)
            ->where('refTypeID', '96')
            ->orWhere('refTypeID', '97')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get a Corporations Bounty Prizes for a specific year / month
     *
     * @param      $corporation_id
     * @param null $year
     * @param null $month
     *
     * @return array|static[]
     */
    public function getCorporationLedgerBountyPrizeByMonth($corporation_id,
                                                           $year = null,
                                                           $month = null)
    {

        return DB::table('corporation_wallet_journals')
            ->select(
                DB::raw(
                    'MONTH(date) as month, YEAR(date) as year, ROUND(SUM(amount)) as total, ownerName2, ownerID2'
                ))
            ->where('corporationID', $corporation_id)
            ->where('refTypeID', '85')
            ->where(DB::raw('YEAR(date)'), !is_null($year) ? $year : date('Y'))
            ->where(DB::raw('MONTH(date)'), !is_null($month) ? $month : date('m'))
            ->groupBy('ownerName2')
            ->orderBy(DB::raw('SUM(amount)'), 'desc')
            ->get();
    }

    /**
     * Get a Corporations PI Payouts for a specific year / month
     *
     * @param      $corporation_id
     * @param null $year
     * @param null $month
     *
     * @return array|static[]
     */
    public function getCorporationLedgerPITotalsByMonth($corporation_id,
                                                        $year = null,
                                                        $month = null)
    {

        return DB::table('corporation_wallet_journals')
            ->select(
                DB::raw(
                    'MONTH(date) as month, YEAR(date) as year, ROUND(SUM(amount)) as total, ownerName1, ownerID1'
                ))
            ->where('corporationID', $corporation_id)
            ->where('refTypeID', '96')
            ->orWhere('refTypeID', '97')
            ->where(DB::raw('YEAR(date)'), !is_null($year) ? $year : date('Y'))
            ->where(DB::raw('MONTH(date)'), !is_null($month) ? $month : date('m'))
            ->groupBy('ownerName1')
            ->orderBy(DB::raw('SUM(amount)'), 'desc')
            ->get();

    }
}
