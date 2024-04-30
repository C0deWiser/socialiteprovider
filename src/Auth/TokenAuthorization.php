<?php

namespace SocialiteProviders\Zenit\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Laravel\Socialite\Facades\Socialite;
use Psr\SimpleCache\CacheInterface;
use SocialiteProviders\Zenit\IntrospectedToken;
use SocialiteProviders\Zenit\rfc7662\TokenIntrospectionInterface;

class TokenAuthorization
{
    protected string $socialiteProvider;
    protected UserProvider $userProvider;
    protected ?CacheInterface $cache;

    public function __construct(
        string $socialiteProvider,
        UserProvider $userProvider,
        ?CacheInterface $cache = null
    )
    {
        $this->cache = $cache;
        $this->userProvider = $userProvider;
        $this->socialiteProvider = $socialiteProvider;
    }

    public function __invoke(Request $request): ?Authenticatable
    {
        $authenticated = $request->user();

        // For test cases
        if ($authenticated) {
            return $authenticated;
        }

        if ($token = $request->bearerToken()) {
            $introspected = $this->introspect($token);

            if ($introspected->isActive() && $introspected->expiresAt() > now()) {
                if ($email = $introspected->sub()) {
                    // Personal token
                    $authenticated = $this->userProvider->retrieveByCredentials(['email' => $email]);
                } else {
                    // App token
                    $authenticated = new Client($introspected);
                }

                if ($authenticated instanceof HasApiTokens) {
                    $authenticated->withAccessToken($introspected);
                }
            }
        }

        return $authenticated;
    }

    public function introspect(string $token): IntrospectedToken
    {
        $key = md5('introspected'.$token);

        $resolved = $this->cache ? $this->cache->get($key) : null;

        if (!$resolved) {
            $provider = Socialite::driver($this->socialiteProvider);

            /** @var IntrospectedToken $resolved */
            $resolved = $provider->introspectToken($token);

            $expires_at = $resolved->expiresAt();

            if ($this->cache && $expires_at) {
                $this->cache->put($key, $resolved, $expires_at);
            }
        }

        return $resolved;
    }
}