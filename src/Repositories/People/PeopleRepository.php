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

namespace Seat\Services\Repositories\People;

use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;
use Seat\Eveapi\Models\Eve\ApiKey;
use Seat\Web\Models\Person;
use Seat\Web\Models\PersonMember;

/**
 * Class PeopleRepository
 * @package Seat\Services\Repositories\People
 */
trait PeopleRepository
{

    /**
     * Get all of the people groups for a user
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getPeopleAllUserPeople()
    {

        if (auth()->user()->has('people.view', false))
            return Person::with('members.characters')->get();

        return Person::whereHas('members.characters', function ($query) {

            $query->whereIn('keyID', ApiKey::where(
                'user_id', auth()->user()->id)->pluck('key_id'));

        })->get();
    }

    /**
     * @param $query
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPeopleSearchListJson($query)
    {

        if (auth()->user()->has('people.view', false))
            return [
                'results' => Person::select('id', 'main_character_name as text')
                    ->where('main_character_name', 'like', '%' . $query . '%')
                    ->orderBy('main_character_name', 'asc')
                    ->get()
            ];

        return [
            'results' => Person::select('id', 'main_character_name as text')
                ->whereHas('members.characters', function ($query) {

                    $query->whereIn('keyID', ApiKey::where(
                        'user_id', auth()->user()->id)->pluck('key_id'));

                })
                ->where('main_character_name', 'like', '%' . $query . '%')
                ->orderBy('main_character_name', 'asc')
                ->get()
        ];
    }

    /**
     * Get all of the API keys that are not part
     * of a specific people group
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getPeopleUnaffiliatedUserKeys()
    {

        $keys = ApiKey::with('characters')
            ->whereNotIn('key_id', function ($query) {

                $query->select('key_id')
                    ->from('person_members');
            });

        if (!auth()->user()->has('apikey.list', false))
            $keys = $keys->where('user_id', auth()->user()->id);

        return $keys->get();

    }

    /**
     * Create a new people group, setting the character
     * as the main and its source key as a member of
     * the people group
     *
     * @param \Seat\Eveapi\Models\Account\ApiKeyInfoCharacters $character
     */
    public function addPeopleNewGroup(ApiKeyInfoCharacters $character)
    {

        $person = Person::create([
            'main_character_id'   => $character->characterID,
            'main_character_name' => $character->characterName
        ]);

        $person->members()->save(
            new PersonMember(['key_id' => $character->keyID]));

        return;

    }

    /**
     * Add an API key to an existing People Group
     *
     * @param $group_id
     * @param $key_id
     *
     * @return static
     */
    public function addPeopleKeyToExistingGroup($group_id, $key_id)
    {

        return PersonMember::create([
            'person_id' => $group_id,
            'key_id'    => $key_id
        ]);
    }

    /**
     * Remove a people group.
     * Member cleanup is done in the Person model
     *
     * @param $group_id
     *
     * @return mixed
     */
    public function removePeopleGroup($group_id)
    {

        return Person::find($group_id)->delete();
    }

    /**
     * Remove a key from a people group. If the people group
     * is empty after the remove, we will delete the people
     * group itself too.
     *
     * @param $key_id
     * @param $group_id
     *
     * @return mixed
     */
    public function removePeopleKeyFromGroup($key_id, $group_id)
    {

        PersonMember::where('key_id', $key_id)
            ->where('person_id', $group_id)
            ->delete();

        if (Person::find($group_id)->members->isEmpty())
            Person::find($group_id)->delete();

        return;

    }

    /**
     * Update a groups main character details
     *
     * @param                                                  $group_id
     * @param \Seat\Eveapi\Models\Account\ApiKeyInfoCharacters $character
     */
    public function setPeopleMainCharacter($group_id, ApiKeyInfoCharacters $character)
    {

        $group = Person::find($group_id);

        $group->fill([
            'main_character_id'   => $character->characterID,
            'main_character_name' => $character->characterName
        ]);

        $group->save();

        return;

    }
}
