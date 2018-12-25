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
    public static function getAuthorEveCharacterID(): ?int
    {
        return null;
    }

    /**
     * Return the plugin author name (or any public nickname).
     *
     * @return string
     */
    abstract public static function getAuthorName(): string;

    /**
     * Return the plugin author e-mail address.
     *
     * @return string|null
     */
    public static function getAuthorMailAddress(): ?string
    {
        return null;
    }

    /**
     * Return the plugin author slack nickname.
     *
     * @return string|null
     */
    public static function getAuthorSlackNickname(): ?string
    {
        return null;
    }

    /**
     * Return the plugin description.
     *
     * @return string|null
     */
    public static function getDescription(): ?string
    {
        return null;
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Return the plugin repository address.
     *
     * @return string
     */
    abstract public static function getPackageRepositoryUrl(): string;

    /**
     * Return the packagist alias.
     *
     * @return string
     */
    public static function getPackagistAlias(): string
    {
        return sprintf('%s/%s',
            call_user_func([get_called_class(), 'getPackagistVendorName']),
            call_user_func([get_called_class(), 'getPackagistPackageName']));
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     */
    abstract public static function getPackagistPackageName(): string;

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     */
    abstract public static function getPackagistVendorName(): string;

    /**
     * Return the plugin installed version.
     *
     * @return string
     */
    abstract public static function getVersion(): string;

    /**
     * Return the package version badge for UI display.
     *
     * @return string
     */
    public static function getVersionBadge(): string
    {
        return sprintf('//img.shields.io/packagist/v/%s/%s.svg?style=flat-square',
            call_user_func([get_called_class(), 'getPackagistVendorName']),
            call_user_func([get_called_class(), 'getPackagistPackageName']));
    }
}
