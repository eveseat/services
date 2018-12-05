<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Mail\MailHeader;
use Seat\Eveapi\Models\Mail\MailMailingList;
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
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function characterTopWalletJournalInteractions(int $character_id): Collection
    {

        // TODO: Optimize this piece of crap!

        return CharacterWalletJournal::with('first_party','second_party')
            ->select('*', DB::raw('count(*) as total'))
            // Limit to the character in question...
            ->where('character_wallet_journals.character_id', $character_id)
            ->groupBy('first_party_id', 'second_party_id')
            ->orderBy('total','desc')
            ->get();

    }

    /**
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function characterTopWalletTransactionInteractions(int $character_id)
    {

        return CharacterWalletTransaction::leftJoin('character_affiliations', function ($join) {

            $join->on(
                'character_affiliations.character_id', '=',
                'character_wallet_transactions.client_id'
            );

        })
            ->where('character_wallet_transactions.character_id', $character_id)
            ->where('character_wallet_transactions.client_id', '<>', $character_id)
            ->select(
                'character_affiliations.character_id',
                'character_affiliations.corporation_id',
                'character_affiliations.alliance_id',
                'character_affiliations.faction_id'
            )
            ->selectRaw('count(client_id) as total')
            ->groupBy('client_id');

    }

    /**
     * @param int $character_id
     *
     * @return mixed
     */
    public function characterTopMailInteractions(int $character_id)
    {

        return MailHeader::leftJoin('character_affiliations', function ($join) {

            $join->on(
                'character_affiliations.character_id', '=',
                'mail_headers.from'
            );

        })
            ->where('mail_headers.character_id', $character_id)
            ->where('mail_headers.from', '<>', $character_id)
            ->whereNotIn('mail_headers.from', MailMailingList::select('mailing_list_id')->distinct()->get())
            ->select(
                'character_affiliations.character_id',
                'character_affiliations.corporation_id',
                'character_affiliations.alliance_id',
                'character_affiliations.faction_id',
                'mail_headers.from'
            )
            ->selectRaw('count(`from`) as total')
            ->groupBy('from');

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

        return CharacterWalletJournal::select(
            DB::raw('count(*) as total'),
            'character_affiliations.character_id',
            'character_affiliations.corporation_id',
            'character_affiliations.alliance_id',
            'character_affiliations.faction_id',
            'standings_profile_standings.elementID as standing_match_on',
            'standings_profile_standings.type as standing_type',
            'standings_profile_standings.standing as standing'
        )->leftJoin('character_affiliations', function ($join) {

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

        })->where('character_wallet_journals.character_id', $character_id)
            ->where('standings_profile_standings.standings_profile_id', $profile_id)
            ->groupBy('standings_profile_standings.elementID');

    }
}
