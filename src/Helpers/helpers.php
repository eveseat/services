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

if (!function_exists('carbon')) {

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
}

if (!function_exists('human_diff')) {

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
}

if (!function_exists('img')) {

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
}

if (!function_exists('number')) {

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
}

if (!function_exists('number_metric')) {

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

        return Coduo\PHPHumanizer\NumberHumanizer::metricSuffix($number);
    }
}

if (!function_exists('clean_ccp_html')) {

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

if (!function_exists('setting')) {

    /**
     * Work with settings.
     *
     * Providing a string argument will retreive a setting.
     * Providing an array arguement will set a setting.
     *
     * @param      $name
     * @param bool $global
     *
     * @return mixed
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
