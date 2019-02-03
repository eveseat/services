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

namespace Seat\Services\Helpers;

use ArrayAccess;

/**
 * Acts as a data store for a Google Analytics Hit.
 *
 * Class AnalyticsContainer
 * @package Seat\Services\Helpers
 */
class AnalyticsContainer implements ArrayAccess
{
    /**
     * A set of default arguments for a Job.
     *
     * @var array
     */
    protected $data = [

        'type' => null,        // Hit Type

        // Event Values
        'ec'   => null,        // Event Category
        'ea'   => null,        // Event Action
        'el'   => null,        // Event Label
        'ev'   => null,        // Event Value

        // Exception Values
        'exd'  => null,        // Exception Description
        'exf'  => null,        // Is Fatal Exception?
    ];

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {

        return array_key_exists($offset, $this->data);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {

        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {

        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {

        unset($this->data[$offset]);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function __get($key)
    {

        return $this[$key];
    }

    /**
     * @param $key
     * @param $val
     */
    public function __set($key, $val)
    {

        $this[$key] = $val;
    }

    /**
     * @param $key
     * @param $val
     *
     * @return $this
     */
    public function set($key, $val)
    {

        $this->__set($key, $val);

        return $this;
    }
}
