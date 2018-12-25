<?php


namespace Seat\Services;


use Illuminate\Support\ServiceProvider;

abstract class AbstractSeatPlugin extends ServiceProvider
{
    /**
     * Return the plugin author EVE Character ID
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
    public abstract static function getAuthorName(): string;

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
    public static abstract function getName(): string;

    /**
     * Return the plugin repository address.
     *
     * @return string
     */
    public static abstract function getPackageRepositoryUrl(): string;

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
    public static abstract function getPackagistPackageName(): string;

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     */
    public static abstract function getPackagistVendorName(): string;

    /**
     * Return the plugin installed version.
     *
     * @return string
     */
    public static abstract function getVersion(): string;

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
