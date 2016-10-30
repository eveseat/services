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

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Character\CharacterSheetSkills;
use Seat\Eveapi\Models\Character\SkillInTraining;
use Seat\Eveapi\Models\Character\SkillQueue;

trait Skills
{

    /**
     * Return the skills detail for a specific Character
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterSkillsInformation(int $character_id) : Collection
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
     * @param int $character_id
     *
     * @return \Seat\Eveapi\Models\Character\SkillInTraining
     */
    public function getCharacterSkillInTraining(int $character_id)
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
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterSkilQueue(int $character_id) : Collection
    {

        return SkillQueue::join('invTypes',
            'character_skill_queues.typeID', '=',
            'invTypes.typeID')
            ->where('characterID', $character_id)
            ->orderBy('queuePosition')
            ->get();

    }

}
