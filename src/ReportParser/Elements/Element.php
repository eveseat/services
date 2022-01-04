<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

namespace Seat\Services\ReportParser\Elements;

use ErrorException;

/**
 * Class Element.
 *
 * @package Seat\Services\ReportParser\Elements
 */
class Element
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * Element constructor.
     *
     * @param  array  $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param $field
     */
    public function add($field)
    {
        array_push($this->fields, $field);
    }

    /**
     * @param $field
     */
    public function remove($field)
    {
        $this->fields = array_filter($this->fields, function ($value, $key) use ($field) {
            return ! ($value === $field || $key === $field);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return $this->fields;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->fields);
    }

    /**
     * @param $name
     * @return mixed
     *
     * @throws \ErrorException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->fields))
            return $this->fields[$name];

        throw new ErrorException(sprintf('Undefined property: %s::$%s', __CLASS__, $name));
    }
}
