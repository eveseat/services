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

namespace Seat\Services\Repositories\Configuration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Seat\Web\Models\Group;
use Seat\Web\Models\User as UserModel;

/**
 * Class User.
 * @package Seat\Services\Repositories
 */
trait UserRespository
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllGroups()
    {

        return Group::all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAllFullUsers() : Builder
    {

        return UserModel::with('refresh_token', 'group.roles')->select('users.*')
            ->where('id', '<>', 1)
            ->orderBy('group_id', 'asc');
    }

    /**
     * @param $user_id
     *
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getFullUser($user_id)
    {

        return UserModel::with('group', 'group.users', 'group.roles.permissions')
            ->where('id', $user_id)
            ->first();
    }

    /**
     * @return mixed
     */
    public function getAllUsersWithKeys()
    {

        $users = UserModel::with('keys.characters')->get();

        return $users;
    }

    /**
     * @param $user_id
     */
    public function flipUserAccountStatus($user_id)
    {

        $user = $this->getUser($user_id);
        $user->active = $user->active == false ? true : false;
        $user->save();

    }

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function getUser($user_id)
    {

        return UserModel::findOrFail($user_id);
    }

    /**
     * Return the characters that are part of a group.
     *
     * @param \Seat\Web\Models\Group $group
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUserGroupCharacters(Group $group = null): Collection
    {

        if (! $group)
            return collect();

        return Group::with('users')->find($group->id)->users;
    }
}
