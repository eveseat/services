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

namespace Seat\Services\Repositories\Seat;

use Seat\Eveapi\Models\Character\CharacterInfoSkill;
use Seat\Eveapi\Models\Industry\CharacterMining;
use Seat\Eveapi\Models\Killmails\CharacterKillmail;
use Seat\Eveapi\Models\Wallet\CharacterWalletBalance;

/**
 * Class Stats.
 * @package Seat\Services\Repositories\Seat
 */
trait Stats
{
    /**
     * @return float
     */
    public function getTotalCharacterIsk(): ?float
    {

        return CharacterWalletBalance::whereIn('character_id',
            auth()->user()->associatedCharacterIds())->sum('balance');
    }

    public function getTotalCharacterMiningIsk()
    {

        return CharacterMining::whereIn('character_id',
            auth()->user()->associatedCharacterIds())->get()->map(function ($item) {

            return $item->amount;
        })->sum();
    }

    /**
     * @return int
     */
    public function getTotalCharacterSkillpoints(): ?int
    {

        return CharacterInfoSkill::whereIn('character_id',
            auth()->user()->associatedCharacterIds())->sum('total_sp');
    }

    /**
     * @return int
     */
    public function getTotalCharacterKillmails(): int
    {

        return CharacterKillmail::whereIn('character_id', auth()->user()->associatedCharacterIds())
            ->count();
    }

    /**
     * @param string $permission  The permission which should be checked
     * @param bool   $corporation True if the permission for which the check should be made is for corporation
     *
     * @return array An array of granted corporationID or characterID
     * @throws \Exception
     */
    private function getUserGrantedEntityList(string $permission, bool $corporation = false): array
    {

        throw new \Exception('Unused method');

        // a list of characterIDs or corporationIDs according to $corporation parameter
        $entities = [];
        // set default entity value to character
        $entity = 'char';
        $entityWildcard = 'character.*';

        // switch entity value to corporation if required
        if ($corporation) {
            $entity = 'corp';
            $entityWildcard = 'corporation.*';
        }

        // get user affiliations
        $affiliations = auth()->user()->getAffiliationMap();

        // check which entity granted access for $permission parameter
        foreach ($affiliations[$entity] as $entityID => $permissions) {

            if (in_array($entityWildcard, $permissions, true) ||
                in_array($permission, $permissions, true))
                $entities[] = $entityID;
        }

        return $entities;
    }
}
