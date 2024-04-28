<?php

namespace SocialiteProviders\Zenit\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Sanctum\Contracts\HasAbilities;
use SocialiteProviders\Zenit\IntrospectedToken;
use SocialiteProviders\Zenit\rfc7662\IntrospectedTokenInterface;

class Bearer implements Authenticatable, HasAbilities
{
    protected IntrospectedTokenInterface $token;

    public function __construct(IntrospectedTokenInterface $token)
    {
        $this->token = $token;
    }

    public function getIntrospectedToken(): IntrospectedTokenInterface
    {
        return $this->token;
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

    public function can($ability): bool
    {
        return in_array($ability, explode(' ', $this->token->scope()));
    }

    public function cant($ability): bool
    {
        return !$this->can($ability);
    }
}