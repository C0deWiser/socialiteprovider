<?php

namespace SocialiteProviders\Zenit\rfc7662;

use SocialiteProviders\Zenit\Exceptions\OAuth2TokenException;

/**
 * OAuth 2.0 Token Introspection.
 *
 * @see https://tools.ietf.org/html/rfc7662
 */
interface TokenIntrospectionInterface
{
    /**
     * Make token introspection.
     *
     * @throws OAuth2TokenException
     */
    public function introspectToken(string $token): IntrospectedTokenInterface;
}