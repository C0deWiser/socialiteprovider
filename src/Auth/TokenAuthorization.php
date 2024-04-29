<?php

namespace SocialiteProviders\Zenit\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Psr\SimpleCache\CacheInterface;
use SocialiteProviders\Zenit\IntrospectedToken;
use SocialiteProviders\Zenit\rfc7662\TokenIntrospectionInterface;

class TokenAuthorization
{
    protected string $providerName;
    protected ?CacheInterface $cache;

    public function __construct(string $socialiteProvider, ?CacheInterface $cache = null)
    {
        $this->cache = $cache;
        $this->providerName = $socialiteProvider;
    }

    public function __invoke(Request $request): ?Authenticatable
    {
        $resolved = $request->user();

        if ($resolved instanceof Client && $resolved->getIntrospectedToken()->isActive()) {
            // Test case
            return $resolved;
        }

        $token = $request->bearerToken();

        if ($token) {

            $resolved = $this->introspect($token);

            if ($resolved->isActive()) {
                return new Client($resolved);
            }
        }

        return null;
    }

    public function introspect(string $token): IntrospectedToken
    {
        $resolved = $this->cache ? $this->cache->get($token) : null;

        if (!$resolved) {
            $provider = Socialite::driver($this->providerName);

            $resolved = $provider->introspectToken($token);

            $expires_at = $resolved->expiresAt();

            if ($this->cache && $expires_at) {
                $this->cache->put($token, $resolved, $expires_at);
            }
        }

        return $resolved;
    }
}