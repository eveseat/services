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

namespace Seat\Services\Repositories\Character;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Account\AccountStatus;
use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;
use Seat\Eveapi\Models\Character\Bookmark;
use Seat\Eveapi\Models\Character\CharacterSheet;
use Seat\Eveapi\Models\Character\CharacterSheetCorporationTitles;
use Seat\Eveapi\Models\Character\CharacterSheetImplants;
use Seat\Eveapi\Models\Character\CharacterSheetSkills;
use Seat\Eveapi\Models\Character\ChatChannel;
use Seat\Eveapi\Models\Character\ContactList;
use Seat\Eveapi\Models\Character\ContactListLabel;
use Seat\Eveapi\Models\Character\KillMail;
use Seat\Eveapi\Models\Character\MailMessage;
use Seat\Eveapi\Models\Character\Notifications;
use Seat\Eveapi\Models\Character\PlanetaryColony;
use Seat\Eveapi\Models\Character\Research;
use Seat\Eveapi\Models\Character\SkillInTraining;
use Seat\Eveapi\Models\Character\SkillQueue;
use Seat\Eveapi\Models\Character\Standing;
use Seat\Eveapi\Models\Character\UpcomingCalendarEvent;
use Seat\Eveapi\Models\Character\WalletJournal;
use Seat\Eveapi\Models\Character\WalletTransaction;
use Seat\Eveapi\Models\Eve\CharacterInfoEmploymentHistory;
use Seat\Services\Helpers\Filterable;

/**
 * Class CharacterRepository
 * @package Seat\Services\Repositories
 */
trait CharacterRepository
{

    use Filterable;

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCharacters()
    {

        return ApiKeyInfoCharacters::all();
    }

    /**
     * Query the databse for characters, keeping filters,
     * permissions and affiliations in mind
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Database\Eloquent\Builder|mixed|static
     */
    public function getAllCharactersWithAffiliationsAndFilters(Request $request = null)
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        $characters = ApiKeyInfoCharacters::with('key', 'key.owner', 'key_info')
            ->join(
                'account_api_key_infos',
                'account_api_key_infos.keyID', '=',
                'account_api_key_info_characters.keyID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID')
            ->join(
                'eve_character_infos',
                'eve_character_infos.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->where('account_api_key_infos.type', '!=', 'Corporation');

        // Apply any received filters
        if ($request && $request->filter)
            $characters = $this->where_filter(
                $characters, $request->filter, config('web.filter.rules.characters'));

        // If the user is a super user, return all
        if (!$user->hasSuperUser()) {

            $characters = $characters->where(function ($query) use ($user, $request) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.list', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });

        }

        return $characters->groupBy('account_api_key_info_characters.characterID')
            ->orderBy('account_api_key_info_characters.characterName')
            ->get();
    }

    /**
     * Return the assets that belong to a Character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterAssets($character_id)
    {

        return DB::table('character_asset_lists as a')
            ->select(DB::raw("
                *, CASE
                when a.locationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID-6000000)
                when a.locationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID-6000001)
                when a.locationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID-6000000)
                when a.locationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID)
                when a.locationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID)
                when a.locationID>=61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID=a.locationID) end
                    AS location,a.locationId AS locID"))
            ->join('invTypes',
                'a.typeID', '=',
                'invTypes.typeID')
            ->join('invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->where('a.characterID', $character_id)
            ->get();
    }

    /**
     * Get a list of alliances the current
     * authenticated user has access to
     *
     * @return mixed
     */
    public function getCharacterAlliances()
    {

        $user = auth()->user();

        $corporations = ApiKeyInfoCharacters::join(
            'eve_api_keys',
            'eve_api_keys.key_id', '=',
            'account_api_key_info_characters.keyID')
            ->join(
                'eve_character_infos',
                'eve_character_infos.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->distinct();

        // If the user us a super user, return all
        if (!$user->hasSuperUser()) {

            $corporations = $corporations->orWhere(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.list', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });
        }

        return $corporations->orderBy('corporationName')
            ->pluck('eve_character_infos.alliance')
            ->filter(function ($item) {

                // Filter out the null alliance name
                return !is_null($item);
            });

    }

    /**
     * Return a characters Bookmarks
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterBookmarks($character_id)
    {

        return Bookmark::where('characterID', $character_id)
            ->get();
    }

    /**
     * Get a characters Chat Channels
     *
     * @param $character_id
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getCharacterChatChannelsFull($character_id)
    {

        return ChatChannel::with('info', 'members')
            ->where('characterID', $character_id)
            ->get();
    }

    /**
     * Get a characters contact list
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterContacts($character_id)
    {

        return ContactList::where('characterID', $character_id)
            ->orderBy('standing', 'desc')
            ->get();
    }

    /**
     * Get a characters contact list labels
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterContactLabels($character_id)
    {

        return ContactListLabel::where('characterID', $character_id)
            ->get();
    }

    /**
     * Get a list of corporations the current
     * authenticated user has access to
     *
     * @return mixed
     */
    public function getCharacterCorporations()
    {

        $user = auth()->user();

        $corporations = ApiKeyInfoCharacters::join(
            'eve_api_keys',
            'eve_api_keys.key_id', '=',
            'account_api_key_info_characters.keyID')
            ->distinct();

        // If the user us a super user, return all
        if (!$user->hasSuperUser()) {

            $corporations = $corporations->orWhere(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.list', false))
                    $query = $query->whereIn('characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });
        }

        return $corporations->orderBy('corporationName')
            ->pluck('corporationName');
    }

    /**
     * Retreive a character name by character id
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterNameById($character_id)
    {

        return ApiKeyInfoCharacters::where('characterID', $character_id)
            ->pluck('characterName');
    }

    /**
     * Get Information about a specific Character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterInformation($character_id)
    {

        return ApiKeyInfoCharacters::join('eve_character_infos',
            'eve_character_infos.characterID', '=',
            'account_api_key_info_characters.characterID')
            ->where('eve_character_infos.characterID', $character_id)
            ->first();

    }

    /**
     * Return the industry jobs for a character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterIndustry($character_id)
    {

        return DB::table('character_industry_jobs as a')
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
                'a.activityID')// character_industry_jobs aliased to a
            ->where('a.characterID', $character_id)
            ->orderBy('endDate', 'desc')
            ->get();
    }

    /**
     * Return the killmails for a character
     *
     * @param     $character_id
     * @param int $chunk
     *
     * @return mixed
     */
    public function getCharacterKillmails($character_id, $chunk = 200)
    {

        return KillMail::select(
            '*',
            'character_kill_mails.characterID as ownerID',
            'kill_mail_details.characterID as victimID')
            ->leftJoin(
                'kill_mail_details',
                'character_kill_mails.killID', '=',
                'kill_mail_details.killID')
            ->leftJoin(
                'invTypes',
                'kill_mail_details.shipTypeID', '=',
                'invTypes.typeID')
            ->leftJoin('mapDenormalize',
                'kill_mail_details.solarSystemID', '=',
                'mapDenormalize.itemID')
            ->where('character_kill_mails.characterID', $character_id)
            ->orderBy('character_kill_mails.killID', 'desc')
            ->paginate($chunk);

    }

    /**
     * Return a characters market orders
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterMarketOrders($character_id)
    {

        return DB::table(DB::raw('character_market_orders as a'))
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
            ->where('a.charID', $character_id)
            ->orderBy('a.issued', 'desc')
            ->get();

    }

    /**
     * Return Contract Information for a character
     *
     * @param     $character_id
     * @param int $chunk
     *
     * @return mixed
     */
    public function getCharacterContracts($character_id, $chunk = 50)
    {

        return DB::table(DB::raw('character_contracts as a'))
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
            ->where('a.characterID', $character_id)
            ->orderBy('dateIssued', 'desc')
            ->paginate($chunk);

    }

    /**
     * Return the character sheet for a character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSheet($character_id)
    {

        return CharacterSheet::find($character_id);
    }

    /**
     * Return the skills detail for a specific Character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSkillsInformation($character_id)
    {

        return CharacterSheetSkills::join('invTypes',
            'character_character_sheet_skills.typeID', '=',
            'invTypes.typeID')
            ->join('invGroups', 'invTypes.groupID', '=', 'invGroups.groupID')
            ->where('character_character_sheet_skills.characterID', $character_id)
            ->orderBy('invTypes.typeName')
            ->get();

    }

    /**
     * Return information about the current skill in training
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSkillInTraining($character_id)
    {

        return SkillInTraining::join('invTypes',
            'character_skill_in_trainings.trainingTypeID', '=',
            'invTypes.typeID')
            ->where('characterID', $character_id)
            ->first();
    }

    /**
     * Return a characters current Skill Queue
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSkilQueue($character_id)
    {

        return SkillQueue::join('invTypes',
            'character_skill_queues.typeID', '=',
            'invTypes.typeID')
            ->where('characterID', $character_id)
            ->orderBy('queuePosition')
            ->get();

    }

    /**
     * Retreive Wallet Journal Entries for a Character
     *
     * @param                          $character_id
     * @param int                      $chunk
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function getCharacterWalletJournal($character_id, $chunk = 50, Request $request = null)
    {

        $journal = WalletJournal::leftJoin('eve_ref_types',
            'character_wallet_journals.refTypeID', '=',
            'eve_ref_types.refTypeID')
            ->where('characterID', $character_id);

        // Apply any received filters
        if ($request && $request->filter)
            $journal = $this->where_filter(
                $journal, $request->filter, config('web.filter.rules.character_journal'));

        return $journal->orderBy('date', 'desc')
            ->paginate($chunk);
    }

    /**
     * Retreive Wallet Transaction Entries for a Character
     *
     * @param                               $character_id
     * @param int                           $chunk
     * @param \Illuminate\Http\Request|null $request
     *
     * @return mixed
     */
    public function getCharacterWalletTransactions($character_id, $chunk = 50, Request $request = null)
    {

        $transactions = WalletTransaction::where('characterID', $character_id);

        // Apply any received filters
        if ($request && $request->filter)
            $transactions = $this->where_filter(
                $transactions, $request->filter, config('web.filter.rules.character_transactions'));

        return $transactions->orderBy('transactionDateTime', 'desc')
            ->paginate($chunk);
    }

    /**
     * Return the employment history for a character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterEmploymentHistory($character_id)
    {

        return CharacterInfoEmploymentHistory::where('characterID', $character_id)
            ->orderBy('startDate', 'desc')
            ->get();

    }

    /**
     * Return the implants a certain character currently has
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterImplants($character_id)
    {

        return CharacterSheetImplants::where('characterID', $character_id)
            ->get();
    }

    /**
     * Get jump clones and jump clone locations for a
     * character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterJumpClones($character_id)
    {

        return DB::table(DB::raw(
            'character_character_sheet_jump_clones as a'))
            ->select(DB::raw("
                *, CASE
                when a.locationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID-6000000)
                when a.locationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID-6000001)
                when a.locationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID-6000000)
                when a.locationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID)
                when a.locationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID)
                when a.locationID>=61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID=a.locationID) end
                    AS location,a.locationId AS locID"))
            ->join('invTypes', 'a.typeID', '=', 'invTypes.typeID')
            ->where('a.characterID', $character_id)
            ->get();
    }

    /**
     * Return the Account Status information for a specific
     * character
     *
     * @param $character_id
     */
    public function getCharacterAccountInfo($character_id)
    {

        $key_info = ApiKeyInfoCharacters::where('characterID', $character_id)
            ->leftJoin(
                'account_api_key_infos',
                'account_api_key_infos.keyID', '=',
                'account_api_key_info_characters.keyID')
            ->where('account_api_key_infos.type', '!=', 'Corporation')
            ->first();

        if ($key_info)
            return AccountStatus::find($key_info->keyID);

        return;

    }

    /**
     * Returns the characters on a API Key
     *
     * @param $key_id
     *
     * @return mixed
     */
    public function getCharactersOnApiKey($key_id)
    {

        return ApiKeyInfoCharacters::where('keyID', $key_id)
            ->get();

    }

    /**
     * Return mail for a character
     *
     * @param     $character_id
     * @param int $chunk
     *
     * @return mixed
     */
    public function getCharacterMail($character_id, $chunk = 50)
    {

        return MailMessage::join('character_mail_message_bodies',
            'character_mail_messages.messageID', '=',
            'character_mail_message_bodies.messageID')
            ->where('characterID', $character_id)
            ->take($chunk)
            ->orderBy('sentDate', 'desc')
            ->get();
    }

    /**
     * Retreive a specific message for a character
     *
     * @param $character_id
     * @param $message_id
     *
     * @return mixed
     */
    public function getCharacterMailMessage($character_id, $message_id)
    {

        return MailMessage::join('character_mail_message_bodies',
            'character_mail_messages.messageID', '=',
            'character_mail_message_bodies.messageID')
            ->where('characterID', $character_id)
            ->where('character_mail_messages.messageID', $message_id)
            ->orderBy('sentDate', 'desc')
            ->first();
    }

    /**
     * Get the mail timeline for all of the characters
     * a logged in user has access to. Either by owning the
     * api key with the characters, or having the correct
     * affiliation & role.
     *
     * Supplying the $message_id will return only that
     * mail.
     *
     * @param null $message_id
     *
     * @return mixed
     */
    public function getCharacterMailTimeline($message_id = null)
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        $messages = MailMessage::join('character_mail_message_bodies',
            'character_mail_messages.messageID', '=',
            'character_mail_message_bodies.messageID')
            ->join(
                'account_api_key_info_characters',
                'character_mail_messages.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID');

        // If the user is a super user, return all
        if (!$user->hasSuperUser()) {

            $messages = $messages->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.mail', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });

        }

        // Filter by messageID if its set
        if (!is_null($message_id))
            return $messages->where('character_mail_messages.messageID', $message_id)
                ->first();

        return $messages->groupBy('character_mail_messages.messageID')
            ->orderBy('character_mail_messages.sentDate', 'desc')
            ->paginate(25);
    }

    /**
     * Return notifications for a character
     *
     * @param     $character_id
     * @param int $chunk
     *
     * @return mixed
     */
    public function getCharacterNotifications($character_id, $chunk = 50)
    {

        return Notifications::join('character_notifications_texts',
            'character_notifications.notificationID', '=',
            'character_notifications_texts.notificationID')
            ->join('eve_notification_types',
                'character_notifications.typeID', '=',
                'eve_notification_types.id')
            ->where('characterID', $character_id)
            ->take($chunk)
            ->orderBy('sentDate', 'desc')
            ->get();
    }

    /**
     * Return the Planetary Colonies for a character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterPlanetaryColonies($character_id)
    {

        return PlanetaryColony::where('ownerID', $character_id)
            ->get();
    }

    /**
     * Return the standings for a character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterStandings($character_id)
    {

        return Standing::where('characterID', $character_id)
            ->get();
    }

    /**
     * Return a characters research info
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterResearchAgents($character_id)
    {

        return Research::join(
            'invNames',
            'character_researches.agentID', '=',
            'invNames.itemID')
            ->join(
                'invTypes',
                'character_researches.skillTypeID', '=',
                'invTypes.typeID')
            ->where('characterID', $character_id)
            ->get();
    }

    /**
     * Get Calendar events for a specific character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterUpcomingCalendarEvents($character_id)
    {

        return UpcomingCalendarEvent::where('characterID', $character_id)
            ->get();
    }

    /**
     * Get Corporation titles related to a specific character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterCorporationTitles($character_id)
    {

        return CharacterSheetCorporationTitles::where('characterID', $character_id)
            ->get();
    }

}
