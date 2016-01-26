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

namespace Seat\Services\Helpers;

use Seat\Services\Exceptions\FilterException;

/**
 * Class Filterable
 * @package Seat\Services\Helpers
 */
trait Filterable
{

    /**
     * Apply where() filters to a query. If an array
     * of parameters is found, whereIn is used,
     * otherwise where().
     *
     * Rules are applied to ensure that filters columns
     * are not tampered with.
     *
     * @param       $query
     * @param array $filters
     * @param array $rules
     *
     * @return mixed
     *
     * @throws \Seat\Services\Exceptions\FilterException
     */
    public function where_filter($query, array $filters, array $rules)
    {

        foreach ($filters as $column => $value) {

            if (!in_array($column, $rules))
                throw new FilterException('Filter on ' . $column . ' blocked by rule');

            if (is_array($value))
                $query = $query->whereIn($column, $value);
            else
                $query = $query->where($column, $value);
        }

        return $query;

    }

}
