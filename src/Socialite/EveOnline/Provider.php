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

namespace Seat\Services\Socialite\EveOnline;

use GuzzleHttp\Client;
use Seat\Eseye\Checker\EsiTokenValidator;
use Seat\Eseye\Configuration;
use Seat\Services\Exceptions\EveImageException;
use Seat\Services\Image\Eve;
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
     * @param  string  $state
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
     * @param  string  $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        return $this->validateJwtToken($token);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
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
            'id' => $character_id,
            'name' => $user['name'],
            'nickname' => $user['name'],
            'character_owner_hash' => $user['owner'],
            'scopes' => is_array($user['scp']) ? $user['scp'] : [$user['scp']],
            'expires_on' => $user['exp'],
            'avatar' => $avatar,
        ]);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];

        if ($this->usesPKCE()) {
            $fields['code_verifier'] = $this->request->session()->pull('code_verifier');
        }

        return $fields;
    }

    /**
     * @param  string  $access_token
     * @return array
     *
     * @throws \Exception
     */
    private function validateJwtToken(string $access_token): array
    {
        $config = Configuration::getInstance();
        $config->http_client = Client::class;

        $validator = new EsiTokenValidator();

        return $validator->validateToken(config('eseye.esi.auth.client_id'), $access_token);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenHeaders($code)
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
        ];
    }
}
