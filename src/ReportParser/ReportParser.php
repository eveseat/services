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

namespace Seat\Services\ReportParser;

use Illuminate\Support\Str;
use Seat\Services\ReportParser\Elements\Element;
use Seat\Services\ReportParser\Elements\Group;

/**
 * Class ReportParser.
 *
 * @package Seat\Services\ReportParser
 */
abstract class ReportParser
{
    /**
     * @var string
     */
    protected $line_delimiter = "\r\n";

    /**
     * @var string
     */
    protected $field_delimiter = "\t";

    /**
     * @var string
     */
    protected $header_regex;

    /**
     * @var string
     */
    protected $group_regex;

    /**
     * @var string
     */
    protected $component_regex;

    /**
     * @var \Seat\Services\ReportParser\Elements\Element
     */
    protected $header;

    /**
     * @var \Seat\Services\ReportParser\Elements\Group[]
     */
    protected $groups = [];

    /**
     * @var \Seat\Services\ReportParser\Elements\Element[]
     */
    protected $elements = [];

    /**
     * @param  string  $report
     */
    public function parse(string $report)
    {
        $lines = explode($this->line_delimiter, $report);

        foreach ($lines as $line)
        {

            // split fields using delimiter
            $fields = explode($this->field_delimiter, $line);

            // check if the line is a header and parse it
            if (preg_match_all($this->header_regex, $line))
                $this->header = new Element($fields);

            // check if the line is a group and parse it
            if (preg_match_all($this->group_regex, $line)) {
                $group = new Group($fields[0]);
                array_push($this->groups, $group);
            }

            // check if the line is a component and parse it
            // append the component to last known group
            if (preg_match_all($this->component_regex, $line)) {

                $field_names = [];

                // retrieve header fields if an header ha been set and use its column name as array keys
                // this will allow us to access fields using header name
                if ($this->hasHeader()) {
                    $field_names = collect($this->getHeader()->fields())
                        ->map(function ($field) {
                            return Str::camel($field);
                        })->toArray();
                }

                $field_values = $fields;

                // generate a new component
                $element = new Element(count($field_names) == count($field_values) ? array_combine($field_names, $field_values) : $field_values);

                // in case the report contains groups, append the element to the last known group
                // otherwise, append the element to root
                if ($this->hasGroups()) {
                    $group->add($element);
                } else {
                    array_push($this->elements, $element);
                }
            }
        }
    }

    /**
     * @throws \Seat\Services\ReportParser\Exceptions\InvalidReportException
     */
    abstract public function validate();

    /**
     * @return bool
     */
    public function hasHeader(): bool
    {
        return ! is_null($this->header);
    }

    /**
     * @return \Seat\Services\ReportParser\Elements\Element
     */
    public function getHeader(): Element
    {
        return $this->header;
    }

    /**
     * @return bool
     */
    public function hasGroups(): bool
    {
        return ! empty($this->groups);
    }

    /**
     * @return \Seat\Services\ReportParser\Elements\Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return bool
     */
    public function hasElements(): bool
    {
        return ! empty($this->elements);
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
        return empty($this->groups) && empty($this->elements);
    }
}
