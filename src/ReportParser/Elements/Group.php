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

namespace Seat\Services\ReportParser\Elements;

/**
 * Class Group.
 *
 * @package Seat\Services\ReportParser\Elements
 */
class Group
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Seat\Services\ReportParser\Elements\Element[]
     */
    protected $elements = [];

    /**
     * Group constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param \Seat\Services\ReportParser\Elements\Element $element
     */
    public function add(Element $element)
    {
        array_push($this->elements, $element);
    }

    /**
     * @param \Seat\Services\ReportParser\Elements\Element $element
     */
    public function remove(Element $element)
    {
        $this->elements = array_filter($this->elements, function ($value) use ($element) {
            return $value !== $element;
        });
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Seat\Services\ReportParser\Elements\Element[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }
}
