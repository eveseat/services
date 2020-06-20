<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Services\Socialite\EveOnline;

use Jose\Component\Core\JWKSet;
use Jose\Easy\Load;
use Seat\Services\Exceptions\EveImageException;
use Seat\Services\Image\Eve;
use Seat\Services\Socialite\EveOnline\Checker\Claim\AzpChecker;
use Seat\Services\Socialite\EveOnline\Checker\Claim\NameChecker;
use Seat\Services\Socialite\EveOnline\Checker\Claim\OwnerChecker;
use Seat\Services\Socialite\EveOnline\Checker\Claim\ScpChecker;
use Seat\Services\Socialite\EveOnline\Checker\Claim\SubEveCharacterChecker;
use Seat\Services\Socialite\EveOnline\Checker\Header\TypeChecker;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

/**
 * Class Provider.
 *
 * @package Seat\Services\Socialite\EveOnline
 */
class Provider extends AbstractProvider
{
    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://login.eveonline.com/v2/oauth/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://login.eveonline.com/v2/oauth/token';
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        return $this->validateJwtToken($token);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        $avatar = asset('img/evewho.png');
        $character_id = strtr($user['sub'], ['CHARACTER:EVE:' => '']);

        try {
            $avatar = (new Eve('characters', 'portrait', $character_id, 128))->url(128);
        } catch (EveImageException $e) {
            logger()->error($e->getMessage(), $e->getTrace());
        }

        return (new User)->setRaw($user)->map([
            'id'                   => $character_id,
            'name'                 => $user['name'],
            'nickname'             => $user['name'],
            'character_owner_hash' => $user['owner'],
            'scopes'               => is_array($user['scp']) ? $user['scp'] : [$user['scp']],
            'expires_on'           => $user['exp'],
            'avatar'               => $avatar,
        ]);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), ['grant_type' => 'authorization_code']);
    }

    /**
     * @return string
     */
    private function getJwkUri(): string
    {
        $response = $this->getHttpClient()
            ->get('https://login.eveonline.com/.well-known/oauth-authorization-server');

        $metadata = json_decode($response->getBody());

        return $metadata->jwks_uri;
    }

    /**
     * @return array An array representing the JWK Key Sets
     */
    private function getJwkSets(): array
    {
        $jwk_uri = $this->getJwkUri();

        $response = $this->getHttpClient()
            ->get($jwk_uri);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $access_token
     * @return array
     * @throws \Exception
     */
    private function validateJwtToken(string $access_token): array
    {
        $scopes = session()->pull('scopes', []);

        // pulling JWK sets from CCP
        $sets = $this->getJwkSets();

        // loading JWK Sets Manager
        $jwk_sets = JWKSet::createFromKeyData($sets);

        // attempt to parse the JWT and collect payload
        $jws = Load::jws($access_token)
            ->algs(['RS256', 'ES256', 'HS256'])
            ->exp()
            ->iss('login.eveonline.com')
            ->header('typ', new TypeChecker(['JWT'], true))
            ->claim('scp', new ScpChecker($scopes))
            ->claim('sub', new SubEveCharacterChecker())
            ->claim('azp', new AzpChecker(config('esi.eseye_client_id')))
            ->claim('name', new NameChecker())
            ->claim('owner', new OwnerChecker())
            ->keyset($jwk_sets)
            ->run();

        return $jws->claims->all();
    }
}
