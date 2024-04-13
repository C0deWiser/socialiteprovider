<?php

namespace SocialiteProviders\Zenit\rfc6749;

use League\OAuth2\Client\Token\AccessTokenInterface;
use SocialiteProviders\Zenit\Exceptions\OAuth2TokenException;

interface GrantRefreshContract extends GrantContract
{
    /**
     * Refresh access_token.
     *
     * @param string $refresh_token
     *
     * @return AccessTokenInterface
     * @throws OAuth2TokenException
     */
    public function grantRefresh(string $refresh_token): AccessTokenInterface;
}