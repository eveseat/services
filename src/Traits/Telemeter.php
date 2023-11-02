<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Services\Traits;

use Exception;
use Illuminate\Support\Str;
use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;

/**
 * Trait Telemeter.
 *
 * @package Seat\Eveapi\Traits
 */
trait Telemeter
{
    use VersionsManagementTrait;

    /**
     * @var \InfluxDB2\Client
     */
    private static $metrics_client;

    /**
     * @param string $endpoint
     * @param int $status
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function sendEsiMetric(string $endpoint, int $status)
    {
        $this->sendTelemetryData([
            'name'   => 'esi',
            'tags'   => ['status' => $status, 'endpoint' => $endpoint],
            'fields' => ['value' => 1],
            'time'   => now()->timestamp,
        ]);
    }

    /**
     * @param int $tokens
     * @param int $characters
     * @param int $corporations
     *
     * @example FluxDB usage
     *
     * from(bucket: "seat")
     * |> range(start: v.timeRangeStart, stop: v.timeRangeStop)
     * |> filter(fn: (r) => r["_measurement"] == "load")
     * |> aggregateWindow(every: v.windowPeriod, fn: mean, createEmpty: false)
     * |> yield(name: "mean")
     */
    public function sendLoadMetrics(int $tokens, int $characters, int $corporations)
    {
        $this->sendTelemetryData([
            'name' => 'load',
            'fields' => [
                'tokens' => $tokens,
                'characters' => $characters,
                'corporations' => $corporations,
            ],
            'time' => now()->timestamp,
        ]);
    }

    /**
     * @throws \Seat\Services\Exceptions\SettingException
     *
     * @example FluxDB usage
     *
     * from(bucket: "seat")
     * |> range(start: v.timeRangeStart, stop: v.timeRangeStop)
     * |> filter(fn: (r) => r._measurement == "environment")
     * |> filter(fn: (r) => r._field == "api")
     * |> group(columns: ["_value"])
     * |> map(fn: (r) => ({_time: r._time, _value: r._value, _field: r._field, index:1}))
     * |> cumulativeSum(columns: ["index"])
     * |> last()
     */
    public function sendEnvironmentMetrics()
    {
        $versions = $this->getPluginsMetadataList()->core->mapWithKeys(function ($package) {
            return [
                $package->getPackagistPackageName() => $package->getVersion(),
            ];
        });

        $this->sendTelemetryData([
            'name' => 'environment',
            'fields' => array_merge([
                'php' => phpversion(),
                'os' => sprintf('%s/%s', php_uname('s'), php_uname('r')),
            ], $versions->toArray()),
            'time' => now()->timestamp,
        ]);
    }

    /**
     * @param array $data
     */
    private function sendTelemetryData(array $data)
    {
        // prevent telemetry data to be sent when tracking is disabled.
        if (setting('allow_tracking', true) === 'no')
            return;

        $client = $this->getMetricsClient();

        try {
            $stream = $client->createWriteApi();
            $stream->write($data);
        } catch (Exception $exception) {
            logger()->warning('Unable to send telemetry data.', [
                'code' => $exception->getCode(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return \InfluxDB2\Client
     */
    private function getMetricsClient()
    {
        if (! is_null(self::$metrics_client))
            return self::$metrics_client;

        self::$metrics_client = new Client([
            'url'       => config('services.config.telemetry.influxdb.url'),
            'token'     => config('services.config.telemetry.influxdb.token'),
            'bucket'    => config('services.config.telemetry.influxdb.bucket'),
            'org'       => config('services.config.telemetry.influxdb.organization'),
            'precision' => WritePrecision::S,
            'tags' => [
                'instance' => $this->getTelemetryInstanceID(),
            ],
        ]);

        return self::$metrics_client;
    }

    /**
     * Return the telemetry instance ID.
     *
     * @return string
     * @throws \Seat\Services\Exceptions\SettingException
     */
    private function getTelemetryInstanceID()
    {
        $id = setting('analytics_id', true);

        if (! $id) {

            // Generate a V4 random UUID
            $id = Str::uuid();

            // Set the generated UUID in the applications config
            setting(['analytics_id', $id], true);
        }

        return $id;
    }
}
