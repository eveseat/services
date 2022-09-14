<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

interface EsiToken
{
    /**
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * @param  string  $token
     * @return \Seat\Services\Contracts\EsiToken
     */
    public function setAccessToken(string $token): self;

    /**
     * @return string
     */
    public function getRefreshToken(): string;

    /**
     * @param  string  $token
     * @return \Seat\Services\Contracts\EsiToken
     */
    public function setRefreshToken(string $token): self;

    /**
     * @return \DateTime
     */
    public function getExpiresOn(): \DateTime;

    /**
     * @param  \DateTime  $expires
     * @return \Seat\Services\Contracts\EsiToken
     */
    public function setExpiresOn(\DateTime $expires): self;

    /**
     * @return array
     */
    public function getScopes(): array;

    /**
     * @param  string  $scope
     * @return bool
     */
    public function hasScope(string $scope): bool;

    /**
     * @return bool
     */
    public function isExpired(): bool;
}
