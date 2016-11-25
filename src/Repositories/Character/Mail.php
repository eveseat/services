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

use Seat\Eveapi\Models\Character\MailMessage;

/**
 * Class Mail
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
     * @return mixed
     */
    public function getAllCharacterNewestMail(int $limit = 10)
    {

        $user = auth()->user();

        $messages = MailMessage::join(
            'account_api_key_info_characters',
            'character_mail_messages.characterID', '=',
            'account_api_key_info_characters.characterID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID');

        // If the user is a super user, return all
        if (!$user->hasSuperUser()) {

            $messages = $messages->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.mail', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });

        }

        return $messages->groupBy('character_mail_messages.messageID')
            ->orderBy('character_mail_messages.sentDate', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Return mail for a character
     *
     * @param int  $character_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return
     */
    public function getCharacterMail(
        int $character_id, bool $get = true, int $chunk = 50)
    {

        $mail = MailMessage::join('character_mail_message_bodies',
            'character_mail_messages.messageID', '=',
            'character_mail_message_bodies.messageID')
            ->where('characterID', $character_id);

        if ($get)
            return $mail->take($chunk)
                ->orderBy('sentDate', 'desc')
                ->get();

        return $mail;
    }

    /**
     * Retreive a specific message for a character
     *
     * @param int $character_id
     * @param int $message_id
     *
     * @return \Seat\Eveapi\Models\Character\MailMessage
     */
    public function getCharacterMailMessage(int $character_id, int $message_id): MailMessage
    {

        return MailMessage::join('character_mail_message_bodies',
            'character_mail_messages.messageID', '=',
            'character_mail_message_bodies.messageID')
            ->where('characterID', $character_id)
            ->where('character_mail_messages.messageID', $message_id)
            ->orderBy('sentDate', 'desc')
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

        $messages = MailMessage::join('character_mail_message_bodies',
            'character_mail_messages.messageID', '=',
            'character_mail_message_bodies.messageID')
            ->join(
                'account_api_key_info_characters',
                'character_mail_messages.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID');

        // If the user is a super user, return all
        if (!$user->hasSuperUser()) {

            $messages = $messages->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.mail', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });

        }

        // Filter by messageID if its set
        if (!is_null($message_id))
            return $messages->where('character_mail_messages.messageID', $message_id)
                ->first();

        return $messages->groupBy('character_mail_messages.messageID')
            ->orderBy('character_mail_messages.sentDate', 'desc')
            ->paginate(25);
    }

}