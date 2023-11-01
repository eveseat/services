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

if (! function_exists('carbon')) {

    /**
     * A helper to get a fresh instance of Carbon.
     *
     * @param  null  $data
     * @return \Carbon\Carbon
     */
    function carbon($data = null)
    {

        if (! is_null($data))
            return new \Carbon\Carbon($data);

        return new \Carbon\Carbon;
    }
}

if (! function_exists('human_diff')) {

    /**
     * Return the time difference from now in a
     * format that humans can read.
     *
     * @param  $time
     * @return string
     */
    function human_diff($time)
    {

        return carbon($time)->diffForHumans();
    }
}

if (! function_exists('img')) {

    /**
     * Return an <img> tag ready for the lazy
     * loading plugin.
     *
     * @param  $type
     * @param  $variation
     * @param  $id
     * @param  $size
     * @param  array  $attr
     * @param  bool|true  $lazy
     * @return string
     *
     * @throws \Seat\Services\Exceptions\EveImageException
     */
    function img(string $type, string $variation, ?int $id, int $size, array $attr = [], $lazy = true)
    {
        $image = (new \Seat\Services\Image\Eve($type, $variation, $id, $size, $attr, $lazy))
            ->html();

        return $image;
    }
}

if (! function_exists('number')) {

    /**
     * Return a formatted number.
     *
     * @param  $number
     * @param  $dec
     * @return string
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    function number($number, $dec = 2)
    {

        return number_format($number, $dec,
            setting('decimal_seperator'), setting('thousand_seperator'));
    }
}

if (! function_exists('number_metric')) {

    /**
     * Return a shortened number with a suffix.
     * Depends on php5-intl.
     *
     * @param  $number
     * @return bool|string
     */
    function number_metric($number)
    {

        return Coduo\PHPHumanizer\NumberHumanizer::metricSuffix($number);
    }
}

if (! function_exists('clean_ccp_html')) {

    /**
     * Strip any CCP styling and tags from an HTML string.
     *
     * @param  $html
     * @param  string  $acceptable_tags
     * @return string
     */
    function clean_ccp_html($html, $acceptable_tags = '<font><br><i>')
    {

        // Handle Unicode cases.
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        // Remove any tags that we are not interested in,
        // or that is not considered valid HTML anyways.
        $html = strip_tags($html, $acceptable_tags);

        // Prep a DOMDocument so that we can remove font
        // colors and size attributes.
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        foreach ($dom->getElementsByTagName('font') as $tag) {

            $tag->removeAttribute('size');
            $tag->removeAttribute('color');
        }

        // Strip tags again as DOMDocument will add a
        // !DOCTYPE attribute
        return trim(strip_tags($dom->saveHTML(), $acceptable_tags));

    }
}

if (! function_exists('evemail_threads')) {

    /**
     * Attempt to 'thread' evemails based on the separator
     * that is automatically added using the eve client.
     *
     * @param  $message
     * @return \Illuminate\Support\Collection
     */
    function evemail_threads($message)
    {

        // Explode the messages based on the delimiter added
        // by the EVE Online Client.
        $delimiter = '--------------------------------<br>';

        return collect(explode($delimiter, $message))->map(function ($thread) {

            // Message headers array.
            $headers = [
                'subject'    => null,
                'from'       => null,
                'sent'       => null,
                'to'         => null,
                'message'    => null,
                'headers_ok' => false,
            ];

            // Start by setting the full message first.
            $headers['message'] = $thread;

            // Next, parse the headers.
            //
            // Example header:
            // Re: Assistance
            // From: Some Character
            // Sent: 2016.11.22 20:38
            // To: qu1ckkkk
            //
            // Example raw header:
            // Re: Assistance<br>From: Some Character<br>Sent: 2016.11.22 20:38<br>To: qu1ckkkk,  <br>

            // Cleanup the message first:
            $thread = clean_ccp_html($thread, '<br>');

            // Explode on the <br> characters to get an array.
            $thread = explode('<br>', $thread, 5);

            // Ensure the message got 4 parts
            if (count($thread) < 4)
                return $headers;

            // Check and extract the headers.
            //
            // First the subject.
            $headers['subject'] = $thread[0];

            // And then the From characterName
            $headers['from'] = strpos($thread[1], 'From') !== false ?
                ltrim($thread[1], 'From: ') : null;

            // Next the sent date.
            // We make this a carbon object too while we at it.
            try {

                $time = carbon()->createFromFormat(
                    'Y.m.d H:i', ltrim($thread[2], 'Sent: '));

                $headers['sent'] = strpos($thread[2], 'Sent') !== false ? $time : null;

            } catch (InvalidArgumentException $e) {
            }

            // Lastly the To characterName. We need to trim a trailing
            // comma (,) here too.
            $to = ltrim($thread[3], 'To: ');
            $to = rtrim($to, ',  ');
            $headers['to'] = strpos($thread[3], 'To') !== false ? $to : null;

            // Ensure that all of the headers resolved.
            if (
                ! is_null($headers['from']) &&
                ! is_null($headers['sent']) &&
                ! is_null($headers['to'])
            )
                $headers['headers_ok'] = true;

            return $headers;

        });

    }
}

if (! function_exists('setting')) {

    /**
     * Work with settings.
     *
     * Providing a string argument will retrieve a setting.
     * Providing an array argument will set a setting.
     *
     * @param  $name
     * @param  bool  $global
     * @return mixed
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    function setting($name, bool $global = false)
    {

        // If we received an array, it means we want to set.
        if (is_array($name)) {

            // Check that we have at least 2 keys.
            if (count($name) < 2)
                throw new \Seat\Services\Exceptions\SettingException(
                    'Must provide a name and value when setting a setting.');

            // If we have a third element in the array, set it.
            $for_id = $name[2] ?? null;

            if ($global)
                return \Seat\Services\Settings\Seat::set($name[0], $name[1], $for_id);

            return \Seat\Services\Settings\Profile::set($name[0], $name[1], $for_id);
        }

        // If we just got a string, it means we want to get.
        if ($global)
            return \Seat\Services\Settings\Seat::get($name);

        return \Seat\Services\Settings\Profile::get($name);

    }
}
if (! function_exists('number_roman')) {
    /**
     * Converts an integer to a roman numberal representation.
     *
     * @param  int  $number
     * @return string
     */
    function number_roman($number)
    {

        $map = [
            'M'  => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50,
            'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1,
        ];

        $returnValue = '';

        while ($number > 0) {

            foreach ($map as $roman => $int) {

                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }

        return $returnValue;
    }
}
