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

namespace Seat\Services\Repositories\Character;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Mail\MailHeader;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;
use Seat\Web\Models\StandingsProfile;

/**
 * Class Intel.
 * @package Seat\Services\Repositories\Character
 */
trait Intel
{
    /**
     * @param \Illuminate\Support\Collection $character_ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterTopWalletJournalInteractions(Collection $character_ids): Builder
    {

        return CharacterWalletJournal::select('first_party_id', 'second_party_id', 'ref_type', 'category', 'entity_id as party_id', 'name as party_name', DB::raw('count(*) as total'),
                DB::raw("
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT corporation_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT corporation_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1)
                end AS corporation_id,
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT alliance_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT alliance_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1) 
                end AS alliance_id, 
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT faction_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT faction_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1)
                end AS faction_id
                ")
            )
            ->leftJoin('universe_names', 'universe_names.entity_id', '=', 'character_wallet_journals.first_party_id')
            ->whereIn('character_wallet_journals.character_id', $character_ids->toArray())
            ->whereNotIn('character_wallet_journals.first_party_id', $character_ids->toArray())
            ->groupBy('first_party_id', 'second_party_id', 'ref_type', 'category', 'party_id', 'party_name')
            ->union(
                CharacterWalletJournal::select('first_party_id', 'second_party_id', 'ref_type', 'category', 'entity_id as party_id', 'name as party_name', DB::raw('count(*) as total'), DB::raw("CASE when universe_names.category = 'character' then (SELECT corporation_id FROM character_affiliations WHERE character_id = universe_names.entity_id) when universe_names.category = 'corporation' then (SELECT corporation_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1) end AS corporation_id, CASE when universe_names.category = 'character' then (SELECT alliance_id FROM character_affiliations WHERE character_id = universe_names.entity_id) when universe_names.category = 'corporation' then (SELECT alliance_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1) end AS alliance_id, CASE when universe_names.category = 'character' then (SELECT faction_id FROM character_affiliations WHERE character_id = universe_names.entity_id) when universe_names.category = 'corporation' then (SELECT faction_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1) end AS faction_id"))
                ->leftJoin('universe_names', 'universe_names.entity_id', '=', 'character_wallet_journals.second_party_id')
                ->whereIn('character_wallet_journals.character_id', $character_ids->toArray())
                ->whereNotIn('character_wallet_journals.second_party_id', $character_ids->toArray())
                ->groupBy('first_party_id', 'second_party_id', 'ref_type', 'category', 'party_id', 'party_name')
            )
            ->orderBy('total', 'desc');
    }

    /**
     * @param \Illuminate\Support\Collection $character_ids
     * @param int $first_party_id
     * @param int $second_party_id
     * @param string $ref_type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterWalletJournalInteractions(Collection $character_ids, int $first_party_id, int $second_party_id, string $ref_type): Builder
    {

        return CharacterWalletJournal::with('first_party', 'second_party')
            ->whereIn('character_id', $character_ids->toArray())
            ->where('first_party_id', '=', $first_party_id)
            ->where('second_party_id', '=', $second_party_id)
            ->where('ref_type', '=', $ref_type);

    }

    /**
     * @param \Illuminate\Support\Collection $character_ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterTopWalletTransactionInteractions(Collection $character_ids): Builder
    {

        return CharacterWalletTransaction::select('client_id', 'category', 'entity_id as party_id', 'name as party_name', DB::raw('count(*) as total'),
                DB::raw("
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT corporation_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT corporation_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1)
                end AS corporation_id,
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT alliance_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT alliance_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1) 
                end AS alliance_id, 
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT faction_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT faction_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1)
                end AS faction_id
                ")
            )
            ->leftJoin('universe_names', 'universe_names.entity_id', '=', 'character_wallet_transactions.client_id')
            ->whereIn('character_wallet_transactions.character_id', $character_ids->toArray())
            ->whereNotIn('character_wallet_transactions.client_id', $character_ids->toArray())
            ->groupBy('client_id', 'category', 'party_id', 'party_name')
            ->orderBy('total', 'desc');

    }

    /**
     * @param \Illuminate\Support\Collection $character_ids
     * @param int $client_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterWalletTransactionInteraction(Collection $character_ids, int $client_id): Builder
    {

        return CharacterWalletTransaction::with('party', 'type', 'location')
            ->select(DB::raw('
            *, CASE
                when character_wallet_transactions.location_id BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=character_wallet_transactions.location_id-6000000)
                when character_wallet_transactions.location_id BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=character_wallet_transactions.location_id-6000001)
                when character_wallet_transactions.location_id BETWEEN 66014934 AND 67999999 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id=character_wallet_transactions.location_id-6000000)
                when character_wallet_transactions.location_id BETWEEN 60014861 AND 60014928 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id=character_wallet_transactions.location_id)
                when character_wallet_transactions.location_id BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=character_wallet_transactions.location_id)
                when character_wallet_transactions.location_id BETWEEN 61000000 AND 61001146 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id=character_wallet_transactions.location_id)
                when character_wallet_transactions.location_id > 61001146 then
                    (SELECT name FROM `universe_structures` AS c
                     WHERE c.structure_id = character_wallet_transactions.location_id)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID=character_wallet_transactions.location_id) end
                AS locationName'
            ))
            ->whereIn('character_id', $character_ids)
            ->where('client_id', $client_id);
    }

    /**
     * @param \Illuminate\Support\Collection $character_ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterTopMailInteractions(Collection $character_ids): Builder
    {
        return MailHeader::select('from', 'entity_id as character_id', 'name as character_name', DB::raw('COUNT(*) as total'),
                DB::raw("
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT corporation_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT corporation_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1)
                end AS corporation_id,
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT alliance_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT alliance_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1) 
                end AS alliance_id, 
                CASE 
                    when universe_names.category = 'character' then 
                        (SELECT faction_id FROM character_affiliations WHERE character_id = universe_names.entity_id) 
                    when universe_names.category = 'corporation' then 
                        (SELECT faction_id FROM character_affiliations WHERE corporation_id = universe_names.entity_id LIMIT 1)
                end AS faction_id
                ")
            )
            ->leftJoin('universe_names', 'mail_headers.from', '=', 'universe_names.entity_id')
            ->leftJoin('mail_recipients', 'mail_headers.mail_id', '=', 'mail_recipients.mail_id')
            ->whereIn('recipient_id', $character_ids->toArray())
            ->whereNotIn('from', $character_ids->toArray())
            ->groupBy('from', 'entity_id', 'category', 'name')
            ->orderBy('total', 'desc');
    }

    /**
     * @param \Illuminate\Support\Collection $character_ids
     * @param int $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getMailContent(Collection $character_ids, int $from): Builder
    {

        return MailHeader::with('body', 'sender', 'recipients')
            ->select('mail_headers.mail_id', 'subject', 'from', 'timestamp')
            ->leftJoin('mail_recipients', 'mail_headers.mail_id', '=', 'mail_recipients.mail_id')
            ->where('from', $from)
            ->whereIn('recipient_id', $character_ids->toArray());
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function standingsProfiles(): Collection
    {

        return StandingsProfile::with('standings')
            ->get();
    }

    /**
     * @param int $profile_id
     *
     * @return \Seat\Web\Models\StandingsProfile
     */
    public function standingsProfile(int $profile_id): StandingsProfile
    {

        return StandingsProfile::with('standings')
            ->where('id', $profile_id)
            ->first();

    }

    /**
     * @param int $character_id
     * @param int $profile_id
     *
     * @return mixed
     */
    public function getCharacterJournalStandingsWithProfile(int $character_id, int $profile_id)
    {

        return CharacterAffiliation::with('character', 'corporation', 'alliance', 'faction')
            ->select(
                DB::raw('count(*) as total'),
                'character_affiliations.character_id',
                'character_affiliations.corporation_id',
                'character_affiliations.alliance_id',
                'character_affiliations.faction_id',
                'standings_profile_standings.elementID as standing_match_on',
                'standings_profile_standings.type as standing_type',
                'standings_profile_standings.standing as standing'
            )->leftJoin('character_wallet_journals', function ($join) {

                $join->on(
                    'character_affiliations.character_id', '=',
                    'character_wallet_journals.first_party_id'
                );

                $join->orOn(
                    'character_affiliations.character_id', '=',
                    'character_wallet_journals.second_party_id'
                );

            })->join('standings_profile_standings', function ($join) {

                $join->on(
                    'standings_profile_standings.elementID', '=',
                    'character_affiliations.character_id'
                );

                $join->orOn(
                    'standings_profile_standings.elementID', '=',
                    'character_affiliations.corporation_id'
                );

                $join->orOn(
                    'standings_profile_standings.elementID', '=',
                    'character_affiliations.alliance_id'
                );

            })
            ->where('character_wallet_journals.character_id', $character_id)
            ->where('standings_profile_standings.standings_profile_id', $profile_id)
            ->groupBy('elementID', 'character_id', 'corporation_id', 'alliance_id', 'faction_id', 'standing', 'type');

    }
}
