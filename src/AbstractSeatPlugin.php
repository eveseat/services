<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Services;

use Illuminate\Support\ServiceProvider;

/**
 * Class AbstractSeatPlugin.
 * @package Seat\Services
 */
abstract class AbstractSeatPlugin extends ServiceProvider
{
    /**
     * Return the plugin author EVE Character ID.
     *
     * @return int|null
     */
    public function getAuthorEveCharacterID(): ?int
    {
        return null;
    }

    /**
     * Return the plugin author name (or any public nickname).
     *
     * @return string
     */
    abstract public function getAuthorName(): string;

    /**
     * Return the plugin author e-mail address.
     *
     * @return string|null
     */
    public function getAuthorMailAddress(): ?string
    {
        return null;
    }

    /**
     * Return the plugin author slack nickname.
     *
     * @return string|null
     */
    public function getAuthorSlackNickname(): ?string
    {
        return null;
    }

    /**
     * Return the plugin description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return null;
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Return the plugin repository address.
     *
     * @return string
     */
    abstract public function getPackageRepositoryUrl(): string;

    /**
     * Return the packagist alias.
     *
     * @return string
     */
    public function getPackagistAlias(): string
    {
        return sprintf('%s/%s',
            $this->getPackagistVendorName(),
            $this->getPackagistPackageName());
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     */
    abstract public function getPackagistPackageName(): string;

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     */
    abstract public function getPackagistVendorName(): string;

    /**
     * Return the plugin installed version.
     *
     * @return string
     */
    abstract public function getVersion(): string;

    /**
     * Return the package version badge for UI display.
     *
     * @return string
     */
    public function getVersionBadge(): string
    {
        return sprintf('//img.shields.io/packagist/v/%s/%s.svg?style=flat-square',
            $this->getPackagistVendorName(),
            $this->getPackagistPackageName());
    }
}
