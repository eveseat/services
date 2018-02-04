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
use Seat\Eveapi\Models\Character\CharacterSkill;
use Seat\Eveapi\Models\Skills\CharacterSkillQueue;

trait Skills
{
    /**
     * Return the skills detail for a specific Character.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterSkillsInformation(int $character_id): Collection
    {

        return CharacterSkill::join('invTypes',
            'character_skills.skill_id', '=',
            'invTypes.typeID')
            ->join('invGroups', 'invTypes.groupID', '=', 'invGroups.groupID')
            ->where('character_skills.character_id', $character_id)
            ->orderBy('invTypes.typeName')
            ->get();

    }

    /**
     * Return information about the current skill in training.
     *
     * @param int $character_id
     *
     * @return \Seat\Eveapi\Models\Skills\CharacterSkillQueue
     */
    public function getCharacterSkillInTraining(int $character_id)
    {

        return CharacterSkillQueue::join('invTypes',
            'character_skill_queues.skill_id', '=',
            'invTypes.typeID')
            ->where('character_id', $character_id)
            ->orderBy('queue_position')
            ->first();
    }

    /**
     * Return a characters current Skill Queue.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterSkillQueue(int $character_id): Collection
    {

        return CharacterSkillQueue::join('invTypes',
            'character_skill_queues.typeID', '=',
            'invTypes.typeID')
            ->where('character_id', $character_id)
            ->orderBy('queue_position')
            ->get();

    }

    /**
     * Get the numer of skills per Level for a character.
     *
     * @param int $character_id
     *
     * @return array
     */
    public function getCharacterSkillsAmountPerLevel(int $character_id): array
    {

        $skills = CharacterSkill::where('character_id', $character_id)
            ->get();

        return [
            $skills->where('trained_skill_level', 0)->count(),
            $skills->where('trained_skill_level', 1)->count(),
            $skills->where('trained_skill_level', 2)->count(),
            $skills->where('trained_skill_level', 3)->count(),
            $skills->where('trained_skill_level', 4)->count(),
            $skills->where('trained_skill_level', 5)->count(),
        ];
    }

    /**
     * Get a characters skill as well as category completion
     * ration rate.
     *
     * TODO: This is definitely a candidate for a better refactor!
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSkillCoverage($character_id): Collection
    {

        $inGameSkills = DB::table('invTypes')
            ->join(
                'invMarketGroups',
                'invMarketGroups.marketGroupID', '=', 'invTypes.marketGroupID'
            )
            ->where('parentGroupID', '?')// binding at [1]
            ->select(
                'marketGroupName',
                DB::raw('COUNT(invTypes.marketGroupID) * 5 as amount')
            )
            ->groupBy('marketGroupName')
            ->toSql();

        $characterSkills = CharacterSkill::join(
                'invTypes',
                'invTypes.typeID', '=',
                'character_skills.skill_id'
            )
            ->join(
                'invMarketGroups',
                'invMarketGroups.marketGroupID', '=',
                'invTypes.marketGroupID'
            )
            ->where('character_id', '?')// binding at [2]
            ->select(
                'marketGroupName',
                DB::raw('COUNT(invTypes.marketGroupID) * character_skills.trained_skill_level as amount')
            )
            ->groupBy(['marketGroupName', 'trained_skill_level'])
            ->toSql();

        $skills = DB::table(
            DB::raw('(' . $inGameSkills . ') a')
        )
            ->leftJoin(
                DB::raw('(' . $characterSkills . ') b'),
                'a.marketGroupName',
                'b.marketGroupName'
            )
            ->select(
                'a.marketGroupName',
                DB::raw('a.amount AS gameAmount'),
                DB::raw('SUM(b.amount) AS characterAmount')
            )
            ->groupBy(['a.marketGroupName', 'a.amount'])
            ->addBinding(150, 'select')// binding [1]
            ->addBinding($character_id, 'select')// binding [2]
            ->get();

        return $skills;
    }
}
