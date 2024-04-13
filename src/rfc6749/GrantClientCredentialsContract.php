<?php

namespace SocialiteProviders\Zenit\rfc6749;

use League\OAuth2\Client\Token\AccessTokenInterface;
use SocialiteProviders\Zenit\Exceptions\OAuth2TokenException;

interface GrantClientCredentialsContract extends GrantContract
{
    /**
     * Get access_token by client credentials.
     *
     * @param string $scope
     *
     * @return AccessTokenInterface
     * @throws OAuth2TokenException
     */
    public function grantClientCredentials(string $scope = ''): AccessTokenInterface;
}