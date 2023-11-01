<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
use Seat\Services\Models\Note;

/**
 * Class NotableTrait.
 *
 * @package Seat\Services\Traits
 */
trait NotableTrait
{
    /**
     * Add a note.
     *
     * @param  int  $object_id
     * @param  string  $title
     * @param  string  $note
     * @return \Seat\Services\Models\Note
     */
    public static function addNote(
        int $object_id, string $title, string $note): Note
    {

        return Note::create([
            'object_type' => __CLASS__,
            'object_id'   => $object_id,
            'title'       => $title,
            'note'        => $note,
        ]);

    }

    /**
     * Get all of the applicable notes.
     *
     * @param  int  $object_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getNotes(int $object_id): Builder
    {

        return Note::where('object_type', __CLASS__)
            ->where('object_id', $object_id);

    }

    /**
     * Get a single note.
     *
     * @param  int  $object_id
     * @param  int  $note_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getNote(int $object_id, int $note_id): Builder
    {

        return Note::where('object_type', __CLASS__)
            ->where('object_id', $object_id)
            ->where('id', $note_id);

    }

    /**
     * Delete a single note.
     *
     * @param  int  $object_id
     * @param  int  $note_id
     * @return int
     */
    public static function deleteNote(int $object_id, int $note_id): int
    {

        return Note::where('object_type', __CLASS__)
            ->where('object_id', $object_id)
            ->where('id', $note_id)
            ->delete();

    }

    /**
     * Update a single note with a new title or note.
     *
     * @param  int  $object_id
     * @param  int  $note_id
     * @param  string|null  $title
     * @param  string|null  $note
     */
    public static function updateNote(
        int $object_id, int $note_id, string $title = null, string $note = null)
    {

        $note_record = Note::where('object_type', __CLASS__)
            ->where('object_id', $object_id)
            ->where('id', $note_id)
            ->first();

        if (! is_null($title))
            $note_record->title = $title;

        if (! is_null($note))
            $note_record->note = $note;

        $note_record->save();

    }
}
