<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Services\Data;

use Seat\Eveapi\Models\JobTracking;

/**
 * Class Queue.
 * @package Seat\Services
 */
trait Queue
{
    /**
     * Return a count summary of the jobs in
     * the queue.
     *
     * @return array
     */
    public function count_summary()
    {

        $response = [
            'total_jobs'   => JobTracking::count('job_id'),
            'working_jobs' => JobTracking::where('status', 'Working')->count('job_id'),
            'queued_jobs'  => JobTracking::where('status', 'Queued')->count('job_id'),
            'done_jobs'    => JobTracking::where('status', 'Done')->count('job_id'),
            'error_jobs'   => JobTracking::where('status', 'Error')->count('job_id'),
        ];

        return $response;
    }
}
