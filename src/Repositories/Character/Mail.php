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
use Seat\Eveapi\Models\Mail\MailHeader;

/**
 * Class Mail.
 * @package Seat\Services\Repositories\Character
 */
trait Mail
{
    /**
     * Return only the last X amount of mail for affiliation
     * related characters.
     *
     * @param int $limit
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllCharacterNewestMail(int $limit = 10) : Collection
    {

        $user = auth()->user();

        $messages = MailHeader::select('mail_id', 'from', 'character_id', 'subject');

        // If the user is a super user, return all
        if (! $user->hasSuperUser()) {

            $messages = $messages->where(function ($query) use ($user) {

                $characters = [];

                // get all user characters affiliation, including those whose are owned by himself
                foreach ($user->getAffiliationMap()['char'] as $characterID => $permissions) {

                    // check for both character wildcard and character mail permission in order to grant the access
                    if (in_array('character.*', $permissions, true) ||
                        in_array('character.mail', $permissions, true)
                    )
                        $characters[] = $characterID;
                }

                // Add the collected characterID on previous task to mail records filter
                $query->whereIn('character_id', $characters)
                    ->orWhereIn('from', $characters);
            });

        }

        return $messages->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Return mail for a character.
     *
     * @param int  $character_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterMail(
        int $character_id, bool $get = true, int $chunk = 50) : Collection
    {

        $mail = MailHeader::join('mail_bodies', 'mail_bodies.mail_id', '=', 'mail_headers.mail_id')
                          ->where('character_id', $character_id);

        if ($get)
            return $mail->take($chunk)
                ->orderBy('timestamp', 'desc')
                ->get();

        return $mail;
    }

    /**
     * Retreive a specific message for a character.
     *
     * @param int $character_id
     * @param int $message_id
     *
     * @return
     */
    public function getCharacterMailMessage(int $character_id, int $message_id)
    {

        return MailHeader::join('mail_bodies', 'mail_bodies.mail_id', '=', 'mail_headers.mail_id')
            ->where('characterI_id', $character_id)
            ->where('mail_headers.mail_id', $message_id)
            ->orderBy('timestamp', 'desc')
            ->first();
    }

    /**
     * Get the mail timeline for all of the characters
     * a logged in user has access to. Either by owning the
     * api key with the characters, or having the correct
     * affiliation & role.
     *
     * Supplying the $message_id will return only that
     * mail.
     *
     * @param int $message_id
     *
     * @return mixed
     */
    public function getCharacterMailTimeline(int $message_id = null)
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        $messages = MailHeader::join('mail_bodies', 'mail_bodies.mail_id', '=', 'mail_headers.mail_id')
            ->join(
                'mail_recipients',
                'mail_recipients.mail_id', '=',
                'mail_headers.mail_id');

        // If the user is a super user, return all
        if (! $user->hasSuperUser()) {

            $messages = $messages->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.mail', false))
                    $query = $query->whereIn('recipient_id',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('recipient_id', $user->id);
            });

        }

        // Filter by messageID if its set
        if (! is_null($message_id))
            return $messages->where('mail_headers.mail_id', $message_id)
                ->first();

        return $messages->orderBy('timestamp', 'desc')
            ->paginate(25);
    }
}
