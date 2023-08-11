<?php

namespace SocialiteProviders\Zenit;

use ArrayAccess;
use SocialiteProviders\Zenit\rfc7662\IntrospectedTokenInterface;

class IntrospectedToken implements ArrayAccess, IntrospectedTokenInterface
{
    /**
     * Introspection raw attributes.
     *
     * @var array
     */
    public $introspected;

    public function __construct(array $introspected)
    {
        $this->introspected = $introspected;
    }

    public function isActive(): bool
    {
        return (bool)$this->introspected['active'];
    }

    public function scope(): ?string
    {
        return $this->introspected['scope'] ?? null;
    }

    public function clientId(): ?string
    {
        return $this->introspected['client_id'] ?? null;
    }

    public function username(): ?string
    {
        return $this->introspected['username'] ?? null;
    }

    public function tokenType(): ?string
    {
        return $this->introspected['token_type'] ?? null;
    }

    public function exp(): ?int
    {
        return (int)$this->introspected['exp'] ?? null;
    }

    public function iat(): ?int
    {
        return (int)$this->introspected['iat'] ?? null;
    }

    public function nbf(): ?int
    {
        return (int)$this->introspected['int'] ?? null;
    }

    public function sub(): ?string
    {
        return $this->introspected['sub'] ?? null;
    }

    public function aud(): ?string
    {
        return $this->introspected['aud'] ?? null;
    }

    public function iss(): ?string
    {
        return $this->introspected['iss'] ?? null;
    }

    public function jti(): ?string
    {
        return $this->introspected['jti'] ?? null;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->introspected);
    }

    public function offsetGet($offset)
    {
        return $this->introspected[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->introspected[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->introspected[$offset]);
    }
}