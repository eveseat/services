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

namespace Seat\Services\Repositories\Seat;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\CharacterSheet;
use Seat\Eveapi\Models\Character\CharacterSheetSkills;
use Seat\Eveapi\Models\KillMail\Detail;

/**
 * Class Stats
 * @package Seat\Services\Repositories\Seat
 */
trait Stats
{

    /**
     * @return mixed
     */
    public function getTotalCharacterIsk()
    {

        return CharacterSheet::sum('balance');

    }

    /**
     * @return mixed
     */
    public function getTotalCharacterSkillpoints()
    {

        return CharacterSheetSkills::sum('skillpoints');
    }

    /**
     * @return mixed
     */
    public function getTotalCharacterKillmails()
    {

        return Detail::count('killID');
    }

    /**
     * @param int $character_id
     * @return array
     */
    public function getSkillsAmountPerLevel(int $character_id)
    {
        $skills = CharacterSheetSkills::where('characterID', $character_id)
            ->get();

        return [
            $skills->where('level', 0)->count(),
            $skills->where('level', 1)->count(),
            $skills->where('level', 2)->count(),
            $skills->where('level', 3)->count(),
            $skills->where('level', 4)->count(),
            $skills->where('level', 5)->count(),
        ];
    }

    /**
     * @param $character_id
     * @return mixed
     */
    public function getSkillCoverage($character_id)
    {
        $inGameSkills = DB::table('invTypes')
            ->join('invMarketGroups', 'invMarketGroups.marketGroupID', '=', 'invTypes.marketGroupID')
            ->where('parentGroupID', '?')
            ->select('marketGroupName', DB::raw('COUNT(invTypes.marketGroupID) as amount'))
            ->groupBy('marketGroupName')
            ->toSql();

        $characterSkills = DB::table('character_character_sheet_skills')
            ->join('invTypes', 'invTypes.typeID', '=', 'character_character_sheet_skills.typeID')
            ->join('invMarketGroups', 'invMarketGroups.marketGroupID', '=', 'invTypes.marketGroupID')
            ->where('characterID', '?')
            ->select('marketGroupName', DB::raw('COUNT(invTypes.marketGroupID) as amount'))
            ->groupBy('marketGroupName')
            ->toSql();

        $skills = DB::table(DB::raw("($inGameSkills) a"))
            ->leftJoin(DB::raw("($characterSkills) b"), 'a.marketGroupName', 'b.marketGroupName')
            ->select('a.marketGroupName', DB::raw('a.amount AS gameAmount'), DB::raw('b.amount AS characterAmount'))
            ->addBinding(150, 'select')
            ->addBinding($character_id, 'select')
            ->get();

        return $skills;
    }

}
