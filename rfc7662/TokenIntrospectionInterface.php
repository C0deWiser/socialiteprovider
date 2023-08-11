<?php

namespace SocialiteProviders\Zenit\rfc7662;

use SocialiteProviders\Zenit\OAuth2TokenException;

/**
 * OAuth 2.0 Token Introspection.
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