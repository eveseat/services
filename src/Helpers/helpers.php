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

/**
 * A helper to get a fresh instance of Carbon
 *
 * @param $data
 *
 * @return \Carbon\Carbon
 */
function carbon($data)
{

    return new \Carbon\Carbon($data);
}

/**
 * Return the time difference from now in a
 * format that humans can read
 *
 * @param $time
 *
 * @return string
 */
function human_diff($time)
{

    return carbon($time)->diffForHumans();
}

/**
 * Return an <img> tag ready for the lazy
 * loading plugin.
 *
 * @param           $type
 * @param           $id
 * @param           $size
 * @param array     $attr
 * @param bool|true $lazy
 *
 * @return string
 */
function img($type, $id, $size, array $attr, $lazy = true)
{

    $image = (new \Seat\Services\Image\Eve($type, (int)$id, $size, $attr, $lazy))
        ->html();

    return $image;
}

/**
 * Dump the next SQL query to screen with its positional
 * parameters populated.
 *
 * This is purely for debugging purposes.
 *
 * @param bool $stop
 */
function dump_query($stop = false)
{

    \Illuminate\Support\Facades\Event::listen(
        'illuminate.query', function ($query, $params, $time, $conn) use ($stop) {

        $positional = 0;
        $full_query = '';

        foreach (str_split($query) as $char) {

            if ($char === '?') {

                $full_query = $full_query . '"' .
                    $params[$positional] . '"';
                $positional++;

            } else {

                $full_query = $full_query . $char;

            }
        }

        // Check if we should stop execution
        if ($stop)
            dd($full_query, $time . ' miliseconds', 'on ' . $conn);

        var_dump($full_query, $time . ' miliseconds', 'on ' . $conn);

    });

    return;
}

/**
 * Return a formatted number.
 *
 * @param $number
 * @param $dec
 *
 * @return string
 */
function number($number, $dec = 2)
{

    return number_format($number, $dec,
        setting('decimal_seperator'), setting('thousand_seperator'));
}

/**
 * Return a shortened number with a suffix.
 * Depends on php5-intl
 *
 * @param $number
 *
 * @return bool|string
 */
function number_metric($number)
{

    return Coduo\PHPHumanizer\Number::metricSuffix($number);
}

/**
 * Strip any CCP styling and tags from an HTML string
 *
 * @param $html
 *
 * @return string
 */
function clean_ccp_html($html)
{

    // The list of tags that is OK to remain.
    $acceptable_tags = '<font><br><i>';

    // Handle Unicode cases.
    $html = mb_convert_encoding($html , 'HTML-ENTITIES', 'UTF-8');

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

/**
 * Retrive a Setting value
 *
 * @param      $name
 * @param bool $global
 *
 * @return mixed
 */
function setting($name, $global = false)
{

    if ($global)
        return \Seat\Services\Settings\Seat::get($name);

    return \Seat\Services\Settings\Profile::get($name);

}
