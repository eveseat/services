<?php

namespace Seat\Services\Helpers;

use Composer\InstalledVersions;
use OutOfBoundsException;
use Seat\Services\AbstractSeatPlugin;
use Seat\Services\Exceptions\SettingException;

/**
 * A helper to build user agents for seat packages
 * Structure and terms according to https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent
 */
class UserAgentBuilder
{
    protected ?string $product = null;
    protected ?string $version = null;
    protected array $comments = [];

    /**
     * Set the product part of the user agent
     * @param string $product The new product name
     * @return $this
     */
    public function product(string $product): UserAgentBuilder {
        $this->product = $product;

        return $this;
    }

    /**
     * Set the version of the product
     * @param string $version
     * @return $this
     */
    public function version(string $version): UserAgentBuilder {
        $this->version = $version;

        return $this;
    }

    /**
     * Configures the product and version of the user agent for a seat plugin
     * @param string|AbstractSeatPlugin $plugin A plugin service provider instance or the FCQN of the service provider (e.g. MyServiceProvider::class)
     * @return $this
     */
    public function seatPlugin(string|AbstractSeatPlugin $plugin): UserAgentBuilder {
        if(is_string($plugin)){
            $plugin = new $plugin(null);
        }

        $this->packagist($plugin->getPackagistVendorName(), $plugin->getPackagistPackageName());

        return $this;
    }

    /**
     * Configures the product and version of the user agent for a packagist package
     * @param string $vendor The packagist vendor name
     * @param string $package The packagist package name
     * @return $this
     */
    public function packagist(string $vendor, string $package): UserAgentBuilder {
        $this->product = sprintf('%s:%s',$vendor, $package);
        $this->version = $this->getPackageVersion($vendor, $package);

        return $this;
    }

    /**
     * Adds a comment containing the product and version of the user agent for a seat plugin
     * @param string|AbstractSeatPlugin $plugin A plugin service provider instance or the FCQN of the service provider (e.g. MyServiceProvider::class)
     * @return $this
     */
    public function commentSeatPlugin(string|AbstractSeatPlugin $plugin): UserAgentBuilder {
        if(is_string($plugin)){
            $plugin = new $plugin(null);
        }

        $this->commentPackagist($plugin->getPackagistVendorName(), $plugin->getPackagistPackageName());

        return $this;
    }

    /**
     * Adds a comment containing the product and version of the user agent for a packagist package
     * @param string $vendor The packagist vendor name
     * @param string $package The packagist package name
     * @return $this
     */
    public function commentPackagist(string $vendor, string $package): UserAgentBuilder {
        $this->comment(sprintf('%s:%s/%s',$vendor, $package, $this->getPackageVersion($vendor, $package)));

        return $this;
    }

    /**
     * Add a comment to the user agent
     * @param string $comment the comment
     * @return $this
     */
    public function comment(string $comment): UserAgentBuilder {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Adds a reasonable set of default comments for any seat plugin.
     * @return $this
     * @throws SettingException
     */
    public function defaultComments(): UserAgentBuilder {
        $this->comment(sprintf('(admin contact: %s)', setting('admin_contact', true) ?? 'not specified'));
        $this->comment('(https://github.com/eveseat/seat)');
        $this->commentPackagist('eveseat','seat');
        $this->commentPackagist('eveseat','web');
        $this->commentPackagist('eveseat','eveapi');
        return $this;
    }

    /**
     * Assembles the user agent form its product, version, and comments into a string
     * @return string The user agent
     */
    public function build(): string {
        if($this->product === null || $this->version===null) {
            throw new \Error('version or product not set.');
        }
        return sprintf('%s/%s %s', $this->product, $this->version, implode(' ',$this->comments));
    }

    /**
     * Gets the installed version of a packagist package
     * @param string $vendor The packagist vendor name
     * @param string $package The packagist package name
     * @return string
     */
    private function getPackageVersion(string $vendor, string $package): string {
        try {
            return InstalledVersions::getPrettyVersion(sprintf('%s/%s', $vendor, $package)) ?? 'unknown';
        } catch (OutOfBoundsException $e) {
            return 'unknown';
        }
    }
}