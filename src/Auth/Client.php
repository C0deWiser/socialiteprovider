<?php

namespace SocialiteProviders\Zenit\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Sanctum\Contracts\HasAbilities;
use Laravel\Sanctum\Contracts\HasApiTokens;
use SocialiteProviders\Zenit\IntrospectedToken;
use SocialiteProviders\Zenit\rfc7662\IntrospectedTokenInterface;

class Client implements Authenticatable, HasApiTokens
{
    protected HasAbilities $token;

    public function __construct(IntrospectedTokenInterface $token)
    {
        $this->token = $token;
    }

    public function getAuthIdentifierName(): string
    {
        return 'client_id';
    }

    public function getAuthIdentifier()
    {
        return $this->token->clientId();
    }

    public function getAuthPasswordName(): string
    {
        return 'client_secret';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value)
    {
        //
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function tokens()
    {
        return [];
    }

    public function tokenCan(string $ability)
    {
        return $this->token->can($ability);
    }

    public function createToken(string $name, array $abilities = ['*'])
    {
        return null;
    }

    public function currentAccessToken(): IntrospectedTokenInterface
    {
        return $this->token;
    }

    public function withAccessToken($accessToken)
    {
        $this->token = $accessToken;
    }
}