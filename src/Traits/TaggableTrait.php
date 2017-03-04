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

namespace Seat\Services\Traits;
use Illuminate\Database\Eloquent\Builder;
use Seat\Services\Models\Tag;

/**
 * Class TaggableTrait.
 * @package Seat\Services\Traits
 */
trait TaggableTrait
{
    public static function addTag(int $object_id, string $tag) : Tag
    {
        return Tag::create([
            'object_type' => __CLASS__,
            'object_id' => $object_id,
            'name' => $tag
        ]);
    }

    public static function getTags(int $object_id) : Builder
    {
        return Tag::where('object_type', __CLASS__)
            ->where('object_id', $object_id);
    }

    public static function deleteTag(int $object_id, int $tag_id) : int
    {
        return Tag::where('object_type', __CLASS__)
            ->where('object_id', $object_id)
            ->where('id', $tag_id)
            ->delete();
    }
}