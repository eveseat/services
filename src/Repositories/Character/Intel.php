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

        return CharacterAffiliation::with('character', 'corporation', 'alliance', 'faction')
            ->select('first_party_id', 'second_party_id', 'ref_type', 'character_affiliations.character_id',
                     'corporation_id', 'alliance_id', 'faction_id', DB::raw('count(*) as total'))
            ->leftJoin('character_wallet_journals', function ($join) {
                $join->on('character_affiliations.character_id', '=', 'character_wallet_journals.first_party_id');
                $join->orOn('character_affiliations.corporation_id', '=', 'character_wallet_journals.first_party_id');
            })
            ->whereIn('character_wallet_journals.character_id', $character_ids->toArray())
            ->whereNotIn('character_wallet_journals.first_party_id', $character_ids->toArray())
            ->groupBy('first_party_id', 'second_party_id', 'ref_type',
                      'character_id', 'corporation_id', 'alliance_id', 'faction_id')
            ->union(
                CharacterAffiliation::with('character', 'corporation', 'alliance', 'faction')
                ->select('first_party_id', 'second_party_id', 'ref_type', 'character_affiliations.character_id',
                         'corporation_id', 'alliance_id', 'faction_id', DB::raw('count(*) as total'))
                ->leftJoin('character_wallet_journals', function ($join) {
                    $join->on('character_affiliations.character_id', '=', 'character_wallet_journals.second_party_id');
                    $join->orOn('character_affiliations.corporation_id', '=', 'character_wallet_journals.second_party_id');
                })
                ->whereIn('character_wallet_journals.character_id', $character_ids->toArray())
                ->whereNotIn('character_wallet_journals.second_party_id', $character_ids->toArray())
                ->groupBy('first_party_id', 'second_party_id', 'ref_type',
                          'character_id', 'corporation_id', 'alliance_id', 'faction_id')
            )
            ->orderBy('total', 'desc');
    }

    /**
     * @param int $first_party_id
     * @param int $second_party_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterWalletJournalInteractions(int $first_party_id, int $second_party_id) : Builder
    {

        return CharacterWalletJournal::with('first_party', 'second_party')
            ->where('first_party_id', '=', $first_party_id)
            ->where('second_party_id', '=', $second_party_id);

    }

    /**
     * @param \Illuminate\Support\Collection $character_ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterTopWalletTransactionInteractions(Collection $character_ids) : Builder
    {

        return CharacterAffiliation::with('character', 'corporation', 'alliance', 'faction')
            ->select('client_id', 'character_affiliations.character_id', 'corporation_id', 'alliance_id', 'faction_id', DB::raw('count(*) as total'))
            ->leftJoin('character_wallet_transactions', function ($join) {
                $join->on('character_affiliations.character_id', '=', 'character_wallet_transactions.client_id');
                $join->orOn('character_affiliations.corporation_id', '=', 'character_wallet_transactions.client_id');
            })
            ->whereIn('character_wallet_transactions.character_id', $character_ids->toArray())
            ->whereNotIn('character_wallet_transactions.client_id', $character_ids->toArray())
            ->groupBy('client_id', 'character_id', 'corporation_id', 'alliance_id', 'faction_id')
            ->orderBy('total', 'desc');

    }

    /**
     * @param int $character_id
     * @param int $client_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterWalletTransactionInteraction(int $character_id, int $client_id) : Builder
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
            ->where('character_id', $character_id)
            ->where('client_id', $client_id);
    }

    /**
     * @param \Illuminate\Support\Collection $character_ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function characterTopMailInteractions(Collection $character_ids) : Builder
    {
        return CharacterAffiliation::with('character', 'corporation', 'alliance', 'faction')
            ->select('from', 'character_affiliations.character_id', 'corporation_id', 'alliance_id', 'faction_id', DB::raw('count(*) as total'))
            ->leftJoin('mail_headers', function ($join) {
                $join->on('character_affiliations.character_id', '=', 'mail_headers.from');
                $join->orOn('character_affiliations.corporation_id', '=', 'mail_headers.from');
            })
            ->whereIn('mail_headers.character_id', $character_ids->toArray())
            ->whereNotIn('mail_headers.from', $character_ids->toArray())
            ->groupBy('from', 'character_id', 'corporation_id', 'alliance_id', 'faction_id')
            ->orderBy('total', 'desc');
    }

    /**
     * @param int $character_id
     * @param int $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getMailContent(int $character_id, int $from) : Builder
    {

        return MailHeader::with('body', 'recipients', 'sender')
            ->select('mail_id', 'subject', 'from', 'timestamp')
            ->where('character_id', $character_id)
            ->where('from', $from)
            ->distinct();

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
