<?php

namespace SocialiteProviders\Zenit\rfc6749;

use League\OAuth2\Client\Token\AccessTokenInterface;
use SocialiteProviders\Zenit\OAuth2TokenException;

interface GrantContract
{
    /**
     * Get access_token.
     *
     * @param string $grant_type
     * @param array $request
     *
     * @return AccessTokenInterface
     * @throws OAuth2TokenException
     */
    public function grant(string $grant_type, array $request): AccessTokenInterface;
}