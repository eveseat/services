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

namespace Seat\Services\Repositories\Character;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\MailMessage;
use Seat\Eveapi\Models\Character\WalletJournal;
use Seat\Eveapi\Models\Character\WalletTransaction;
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
    public function characterTopWalletJournalInteractions(int $character_id)
    {

        // TODO: Optimize this peice of crap!

        return WalletJournal::select(
            DB::raw('count(*) as total'),
            'eve_ref_types.refTypeName',
            'character_affiliations.characterID',
            'character_affiliations.characterName',
            'character_affiliations.corporationID',
            'character_affiliations.corporationName',
            'character_affiliations.allianceID',
            'character_affiliations.allianceName'
        )
            ->leftJoin('character_affiliations', function ($join) {

                $join->on(
                    'character_affiliations.characterID', '=',
                    'character_wallet_journals.ownerID1'
                );

                $join->orOn(
                    'character_affiliations.characterID', '=',
                    'character_wallet_journals.ownerID2'
                );

            })
            ->join('eve_ref_types', function ($join) {

                $join->on(
                    'eve_ref_types.refTypeID', '=',
                    'character_wallet_journals.refTypeID'
                );
            })
            // Limit to the character in question...
            ->where('character_wallet_journals.characterID', $character_id)
            ->groupBy('ownerID1', 'ownerID2');

    }

    /**
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function characterTopWalletTransactionInteractions(int $character_id)
    {

        return WalletTransaction::leftJoin('character_affiliations', function ($join) {

            $join->on(
                'character_affiliations.characterID', '=',
                'character_wallet_transactions.clientID'
            );

        })
            ->where('character_wallet_transactions.characterID', $character_id)
            ->where('character_wallet_transactions.clientID', '<>', $character_id)
            ->select(
                'character_affiliations.characterID',
                'character_affiliations.characterName',
                'character_affiliations.corporationID',
                'character_affiliations.corporationName',
                'character_affiliations.allianceID',
                'character_affiliations.allianceName'
            )
            ->selectRaw('count(clientID) as total')
            ->groupBy('clientID');

    }

    /**
     * @param int $character_id
     *
     * @return
     */
    public function characterTopMailInteractions(int $character_id)
    {

        return MailMessage::leftJoin('character_affiliations', function ($join) {

            $join->on(
                'character_affiliations.characterID', '=',
                'character_mail_messages.senderID'
            );

        })
            ->where('character_mail_messages.characterID', $character_id)
            ->where('character_mail_messages.senderID', '<>', $character_id)
            ->select(
                'character_affiliations.characterID',
                'character_affiliations.characterName',
                'character_affiliations.corporationID',
                'character_affiliations.corporationName',
                'character_affiliations.allianceID',
                'character_affiliations.allianceName'
            )
            ->selectRaw('count(senderID) as total')
            ->groupBy('senderID');

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
     * @return
     */
    public function getCharacterJournalStandingsWithProfile(int $character_id, int $profile_id)
    {

        return WalletJournal::select(
            DB::raw('count(*) as total'),
            'character_affiliations.characterName',
            'character_affiliations.characterID',
            'character_affiliations.corporationName',
            'character_affiliations.corporationID',
            'character_affiliations.allianceName',
            'character_affiliations.allianceID',
            'standings_profile_standings.elementID as standing_match_on',
            'standings_profile_standings.type as standing_type',
            'standings_profile_standings.standing as standing'
        )->leftJoin('character_affiliations', function ($join) {

            $join->on(
                'character_affiliations.characterID', '=',
                'character_wallet_journals.ownerID1'
            );

            $join->orOn(
                'character_affiliations.characterID', '=',
                'character_wallet_journals.ownerID2'
            );

        })->join('standings_profile_standings', function ($join) {

            $join->on(
                'standings_profile_standings.elementID', '=',
                'character_affiliations.characterID'
            );

            $join->orOn(
                'standings_profile_standings.elementID', '=',
                'character_affiliations.corporationID'
            );

            $join->orOn(
                'standings_profile_standings.elementID', '=',
                'character_affiliations.allianceID'
            );

        })->where('character_wallet_journals.characterID', $character_id)
            ->where('standings_profile_standings.standings_profile_id', $profile_id)
            ->groupBy('standings_profile_standings.elementID');

    }
}
