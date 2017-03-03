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

namespace Seat\Services\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Settings\Seat;

/**
 * Class Analytics.
 * @package Seat\Services\Jobs
 */
class Analytics implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Seat\Services\Helpers\AnalyticsContainer
     */
    private $hit;

    /**
     * @var string
     */
    private $tracking_id = 'UA-80887494-1';

    /**
     * @var bool
     */
    private $debug;

    /**
     * Create a new job instance.
     *
     * @param \Seat\Services\Helpers\AnalyticsContainer $hit
     * @param bool                                      $debug
     */
    public function __construct(AnalyticsContainer $hit, $debug = false)
    {

        $this->hit = $hit;
        $this->debug = $debug;

    }

    /**
     * Execute the job, keeping in mind that if tracking
     * is disabled, nothing should be sent and the
     * job should just return.
     *
     * @return void
     */
    public function handle()
    {

        // Do nothing if tracking is disabled
        if (! $this->allowTracking())
            return;

        // Send the hit based on the hit type
        switch ($this->hit->type) {

            case 'event':
                $this->sendEvent();
                break;

            case 'exception':
                $this->sendException();
                break;

            default:
                break;
        }

    }

    /**
     * Check if tracking is allowed.
     *
     * @return bool
     */
    public function allowTracking()
    {

        if (Seat::get('allow_tracking') === 'no')
            return false;

        return true;
    }

    /**
     * Send an 'event' type hit to GA.
     */
    public function sendEvent()
    {

        $this->send('event', [
            'ec' => $this->hit->ec,     // Event Category
            'ea' => $this->hit->ea,     // Event Action
            'el' => $this->hit->el,     // Event Label
            'ev' => $this->hit->ev,     // Event Value
        ]);

    }

    /**
     * Send the GA Hit.
     *
     * @param       $type
     * @param array $query
     */
    private function send($type, array $query)
    {

        $client = new Client([
            'base_uri' => 'https://www.google-analytics.com/',
            'timeout'  => 5.0,
        ]);

        // Check if we are in debug mode
        $uri = $this->debug ? '/debug/collect' : '/collect';

        // Submit the hit
        $client->get($uri, [
            'query' => array_merge([

                // Fields referenced from:
                //  https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters

                // Required Fields
                //  https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#required
                'v'   => 1,                     // Protocol Version
                'tid' => $this->tracking_id,    // Google Tracking-ID
                'cid' => $this->getClientID(),  // Unique Client-ID
                't'   => $type,                 // Event

                // Optional Fields
                'aip' => 1,                     // Anonymize the IP of the calling client
                'an'  => 'SeAT',                // App Name

                // Versions of the currently installed packages.
                'av'  => 'api/' . config('api.config.version') . ', ' .
                    'console/' . config('console.config.version') . ', ' .
                    'eveapi/' . config('eveapi.config.version') . ', ' .
                    'notifications/' . config('notifications.config.version') . ', ' .
                    'web/' . config('web.config.version') . ', ' .
                    'services/' . config('services.config.version') . ', ',

                // User Agent is comprised of OS Name(s), Release(r)
                // and Machine Type(m). Examples:
                //  Darwin/15.6.0/x86_64
                //  Linux/2.6.32-642.el6.x86_64/x86_64
                //
                // See:
                //  http://php.net/manual/en/function.php-uname.php
                'ua'  => 'SeAT/' . php_uname('s') .
                    '/' . php_uname('r') .
                    '/' . php_uname('m'),

                'z' => rand(1, 10000),          // Cache Busting Random Value
            ], $query),
        ]);

    }

    /**
     * Retreive a client-id from the cache. If none
     * exists, generate one.
     */
    private function getClientID()
    {

        $id = Seat::get('analytics_id');

        if (! $id) {

            // Generate a V4 random UUID
            //  https://gist.github.com/dahnielson/508447#file-uuid-php-L74
            $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            // Set the generated UUID in the applications config
            Seat::set('analytics_id', $id);
        }

        return $id;

    }

    /**
     * Send an 'exception' type hit to GA.
     */
    public function sendException()
    {

        $this->send('exception', [
            'exd' => $this->hit->exd,   // Exception Description
            'exf' => $this->hit->exf,   // Is Fatal Exception?
        ]);
    }
}
