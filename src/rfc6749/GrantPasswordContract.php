<?php

namespace SocialiteProviders\Zenit\rfc6749;

use League\OAuth2\Client\Token\AccessTokenInterface;
use SocialiteProviders\Zenit\Exceptions\OAuth2TokenException;

interface GrantPasswordContract extends GrantContract
{
    /**
     * Get access_token by username and password.
     *
     * @param string $username
     * @param string $password
     * @param string $scope
     *
     * @return AccessTokenInterface
     * @throws OAuth2TokenException
     */
    public function grantPassword(string $username, string $password, string $scope = ''): AccessTokenInterface;
}