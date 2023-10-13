<?php

namespace Seat\Services\Helpers;

use Composer\InstalledVersions;
use OutOfBoundsException;
use Seat\Services\AbstractSeatPlugin;

class UserAgentBuilder
{
    protected ?string $product = null;
    protected ?string $version = null;
    protected array $comments = [];

    public function product(string $product): UserAgentBuilder {
        $this->product = $product;

        return $this;
    }

    public function version(string $version): UserAgentBuilder {
        $this->version = $version;

        return $this;
    }


    public function seatPlugin(string|AbstractSeatPlugin $plugin): UserAgentBuilder {
        if(is_string($plugin)){
            $plugin = new $plugin(null);
        }

        $this->packagist($plugin->getPackagistVendorName(), $plugin->getPackagistPackageName());

        return $this;
    }

    public function packagist(string $vendor, string $package): UserAgentBuilder {
        $this->product = sprintf('%s:%s',$vendor, $package);
        $this->version = $this->getPackageVersion($vendor, $package);

        return $this;
    }

    public function commentSeatPlugin(string|AbstractSeatPlugin $plugin): UserAgentBuilder {
        if(is_string($plugin)){
            $plugin = new $plugin(null);
        }

        $this->commentPackagist($plugin->getPackagistVendorName(), $plugin->getPackagistPackageName());

        return $this;
    }

    public function commentPackagist(string $vendor, string $package): UserAgentBuilder {
        $this->comment(sprintf('%s:%s/%s',$vendor, $package, $this->getPackageVersion($vendor, $package)));

        return $this;
    }

    public function comment(string $part): UserAgentBuilder {
        $this->comments[] = $part;

        return $this;
    }

    public function defaultComments(): UserAgentBuilder {
        $this->comment(sprintf('(admin contact: %s)', setting('admin_contact', true) ?? 'not specified'));
        $this->comment('(https://github.com/eveseat/seat)');
        $this->commentPackagist('eveseat','seat');
        $this->commentPackagist('eveseat','web');
        $this->commentPackagist('eveseat','eveapi');
        return $this;
    }

    public function build(): string {
        if($this->product === null || $this->version===null) {
            throw new \Error('version or product not set.');
        }
        return sprintf('%s/%s %s', $this->product, $this->version, implode(' ',$this->comments));
    }

    private function getPackageVersion(string $vendor, string $package): string {
        try {
            return InstalledVersions::getPrettyVersion(sprintf('%s/%s', $vendor, $package)) ?? 'unknown';
        } catch (OutOfBoundsException $e) {
            return 'unknown';
        }
    }
}