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

interface EsiResponse
{
    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @param  string  $name
     * @return array
     */
    public function getHeader(string $name): array;

    /**
     * @param  string  $name
     * @return string
     */
    public function getHeaderLine(string $name): string;

    /**
     * @param  string  $name
     * @return bool
     */
    public function hasHeader(string $name): bool;

    /**
     * @return bool
     */
    public function expired(): bool;

    /**
     * @return int|null
     */
    public function getPagesCount(): ?int;

    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return bool
     */
    public function isFailed(): bool;

    /**
     * @return object|array
     */
    public function getBody(): object|array;

    /**
     * @return string
     */
    public function error(): string;

    /**
     * @return bool
     */
    public function isFromCache(): bool;
}
