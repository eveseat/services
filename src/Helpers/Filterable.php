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

namespace Seat\Services\Helpers;

/**
 * Class Filterable
 * @package Seat\Services\Helpers
 */
trait Filterable
{

    /**
     * Apply where() filters to a query. If an array
     * of parameters is found, whereIn is used,
     * otherwise where()
     *
     * @param       $query
     * @param array $filters
     *
     * @return mixed
     */
    public function where_filter($query, array $filters)
    {

        foreach ($filters as $column => $value)

            if (is_array($value))
                $query = $query->whereIn($column, $value);
            else
                $query = $query->where($column, $value);

        return $query;

    }

}
