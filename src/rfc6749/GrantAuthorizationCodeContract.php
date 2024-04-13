<?php

namespace SocialiteProviders\Zenit\rfc6749;

use League\OAuth2\Client\Token\AccessToken;
use SocialiteProviders\Zenit\Exceptions\OAuth2TokenException;

interface GrantAuthorizationCodeContract extends GrantContract
{
    /**
     * Get access_token by authorization code.
     *
     * @param string $code
     * @param string $redirect_uri
     *
     * @return AccessToken
     * @throws OAuth2TokenException
     */
    public function grantAuthorizationCode(string $code, string $redirect_uri): AccessToken;
}