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

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OutOfBoundsException;
use Seat\Services\AbstractSeatPlugin;
use stdClass;

/**
 * Trait VersionsManagementTrait.
 *
 * @package Seat\Services\Traits
 */
trait VersionsManagementTrait
{
    /**
     * Compute a list of provider class which are implementing SeAT package structure.
     *
     * @return \stdClass
     */
    protected function getPluginsMetadataList(): stdClass
    {
        app()->loadDeferredProviders();
        $providers = array_keys(app()->getLoadedProviders());

        $packages = (object) [
            'core' => collect(),
            'plugins' => collect(),
        ];

        foreach ($providers as $class) {
            // attempt to retrieve the class from booted app
            $provider = app()->getProvider($class);

            if (is_null($provider))
                continue;

            // ensure the provider is a valid SeAT package
            if (! is_a($provider, AbstractSeatPlugin::class))
                continue;

            // seed proper collection according to package vendor
            $provider->getPackagistVendorName() === 'eveseat' ?
                $packages->core->push($provider) : $packages->plugins->push($provider);
        }

        return $packages;
    }

    /**
     * @param string $vendor
     * @param string $package
     *
     * @return string
     */
    protected function getPackageLatestVersion(string $vendor, string $package): string
    {
        // construct the packagist uri to its API
        $packagist_url = sprintf('https://packagist.org/packages/%s/%s.json',
            $vendor, $package);

        try {
            $installed_version = InstalledVersions::getPrettyVersion(sprintf('%s/%s', $vendor, $package)) ?? '0.0.0';
        } catch (OutOfBoundsException $e) {
            $installed_version = '0.0.0';
        }

        // retrieve package meta-data
        try {
            $response = (new Client())->request('GET', $packagist_url);

            if ($response->getStatusCode() !== 200)
                return '0.0.0';

            // convert the body into an array
            $json_array = json_decode($response->getBody(), true);

            // in case we miss either versions or package attribute, return an error as those attribute should contains version information
            if (! array_key_exists('package', $json_array) || ! array_key_exists('versions', $json_array['package']))
                return '0.0.0';

            // extract published versions from packagist response
            $versions = $json_array['package']['versions'];

            foreach ($versions as $available_version => $metadata) {

                // ignore any unstable versions
                if (strpos($available_version, 'dev') !== false || strpos($available_version, 'rc') !== false ||
                    strpos($available_version, 'alpha') !== false || strpos($available_version, 'beta') !== false)
                    continue;

                // return outdated on the first package which is greater than installed version
                if (version_compare($installed_version, $metadata['version']) < 0)
                    return $metadata['version'];
            }
        } catch (GuzzleException $e) {
            logger()->error($e->getMessage());

            return 'offline';
        }

        return $installed_version;
    }
}
