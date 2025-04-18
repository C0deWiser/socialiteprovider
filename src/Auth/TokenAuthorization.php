<?php

namespace SocialiteProviders\Zenit\Auth;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Psr\SimpleCache\CacheInterface;
use SocialiteProviders\Zenit\IntrospectedToken;

class TokenAuthorization
{
    protected string $socialiteProvider;
    protected UserProvider $userProvider;
    protected ?CacheInterface $cache;
    protected ?Closure $callback;

    public function __construct(
        string $socialiteProvider,
        UserProvider $userProvider,
        ?CacheInterface $cache = null,
        ?Closure $callback = null
    ) {
        $this->cache = $cache;
        $this->userProvider = $userProvider;
        $this->socialiteProvider = $socialiteProvider;
        $this->callback = $callback;
    }

    public function __invoke(Request $request): ?Authenticatable
    {
        $authenticated = $request->user();

        if (!$authenticated) {
            if ($token = $request->bearerToken()) {
                $introspected = $this->introspect($token);

                if ($introspected->isActive() && $introspected->expiresAt() > now()) {
                    $email = $introspected->sub();
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // Personal token
                        $authenticated = $this->userProvider->retrieveByCredentials(['email' => $email]);
                    } else {
                        // App token
                        $authenticated = new Client($introspected);
                    }

                    if (method_exists($authenticated, 'withAccessToken')) {
                        $authenticated->withAccessToken($introspected);
                    }
                }
            }
        }

        if (is_callable($this->callback)) {
            call_user_func($this->callback, $authenticated, $request);
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