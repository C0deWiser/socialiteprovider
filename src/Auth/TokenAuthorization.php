<?php

namespace SocialiteProviders\Zenit\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Psr\SimpleCache\CacheInterface;
use SocialiteProviders\Zenit\IntrospectedToken;
use SocialiteProviders\Zenit\rfc7662\TokenIntrospectionInterface;

class TokenAuthorization
{
    protected TokenIntrospectionInterface $provider;
    protected ?CacheInterface $cache;

    public function __construct(TokenIntrospectionInterface $provider, ?CacheInterface $cache = null)
    {
        $this->cache = $cache;
        $this->provider = $provider;
    }

    public function __invoke(Request $request): ?Authenticatable
    {
        $resolved = $request->user();

        if ($resolved instanceof Bearer && $resolved->getIntrospectedToken()->isActive()) {
            // Test case
            return $resolved;
        }

        $token = $request->bearerToken();

        if ($token) {

            $resolved = $this->introspect($token);

            if ($resolved->isActive()) {
                return new Bearer($resolved);
            }
        }

        return null;
    }

    public function introspect(string $token): IntrospectedToken
    {
        $resolved = $this->cache ? $this->cache->get($token) : null;

        if (!$resolved) {
            $resolved = $this->provider->introspectToken($token);

            $expires_at = $resolved->expiresAt();

            if ($this->cache && $expires_at) {
                $this->cache->put($token, $resolved, $expires_at);
            }
        }

        return $resolved;
    }
}