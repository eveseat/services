<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

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

namespace Seat\Services\Image;

use Seat\Services\Exceptions\EveImageException;

/**
 * Class Eve
 * @package Seat\Services\Image
 */
class Eve
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var bool
     */
    protected $lazy;

    /**
     * @var array
     */
    protected $known_types = [
        'character', 'corporation', 'alliance', 'type'];

    protected $extention = 'png';

    /**
     * @var string
     */
    protected $img_server = '//image.eveonline.com';

    /**
     * @param           $type
     * @param           $id
     * @param           $size
     * @param array     $attr
     * @param bool|true $lazy
     *
     * @throws \Seat\Services\Exceptions\EveImageException
     */
    public function __construct($type, $id, $size, array $attr, $lazy = true)
    {

        // Validate the arguments
        if (!in_array($type, $this->known_types))
            throw new EveImageException($type . ' is not a valid image type.');

        if (!is_int($id))
            throw new EveImageException('id must be an integer.');

        if (!is_int($size))
            throw new EveImageException('size must be an integer');

        $this->type = ucfirst($type);
        $this->id = $id;
        $this->attributes = $attr;
        $this->lazy = $lazy;

        // Fix images to _at least_ x32
        $this->size = ($size < 32 ? 32 : $size);

        // Character images are jpg, everything else is
        // png. So, set the extention to jpg if this
        // is for a character image
        if ($type == 'character')
            $this->extention = 'jpg';

    }

    /**
     * @return string
     */
    public function html()
    {

        // make new IMG tag
        $html = '<img ';

        if ($this->lazy) {

            // images are lazy loaded. prepare the the data-src attributes with the
            // location for the image.
            $html .= 'src="' . asset('web/img/bg.png') . '" ';
            $html .= 'data-src="' . $this->url($this->size) . '" ';
            $html .= 'data-src-retina="' . $this->url($this->size * 2) . '" ';

            // put class on images to lazy load them
            if (!isset($this->attributes['class']))
                $this->attributes['class'] = '';

            $this->attributes['class'] .= ' img-lazy-load';

        } else {

            // no lazy loaded image
            $html .= 'src="' . $this->url($this->size) . '" ';
        }

        // unset already built attributes
        unset(
            $this->attributes['src'],
            $this->attributes['data-src='],
            $this->attributes['data-src-retina']
        );

        // render other attributes
        foreach ($this->attributes as $name => $value)
            $html .= "{$name}=\"{$value}\" ";

        // close IMG tag
        $html .= ' />';

        // return completed img tag
        return $html;
    }

    /**
     * @param $size
     *
     * @return string
     */
    public function url($size)
    {

        return $this->img_server . '/' . $this->type . '/' . $this->id .
        '_' . $size . '.' . $this->extention;

    }
}
