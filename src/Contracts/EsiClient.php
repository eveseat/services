<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

namespace Seat\Services\Contracts;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

interface EsiClient
{
    /**
     * @return \Seat\Services\Contracts\EsiToken
     */
    public function getAuthentication(): EsiToken;

    /**
     * @param  \Seat\Services\Contracts\EsiToken  $authentication
     * @return $this
     */
    public function setAuthentication(EsiToken $authentication): self;

    /**
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * @return array
     */
    public function getQueryString(): array;

    /**
     * @param  array  $query
     * @return $this
     */
    public function setQueryString(array $query): self;

    /**
     * @param  string  $date
     * @return void
     */
    public function setCompatibilityDate(string $date): self;

    /**
     * @return string
     */
    public function getCompatibilityDate(): string;

    /**
     * @return array
     */
    public function getBody(): array;

    /**
     * @param  array  $body
     * @return $this
     */
    public function setBody(array $body): self;

    /**
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $uri_data
     * @return \Seat\Services\Contracts\EsiResponse
     */
    public function invoke(string $method, string $uri, array $uri_data = []): EsiResponse;

    /**
     * @param  int  $page
     * @return $this
     */
    public function page(int $page): self;

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface;

    /**
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getCache(): CacheInterface;

    public function getValidAccessToken(): string;
}
