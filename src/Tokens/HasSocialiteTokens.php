<?php

namespace SocialiteProviders\Zenit\Tokens;

use DateTimeInterface;
use Illuminate\Support\Str;

trait HasSocialiteTokens
{
    /**
     * The access token the user is using for the current request.
     *
     * @var SocialiteAccessToken
     */
    protected $socialiteToken;

    /**
     * Get the access tokens that belong to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function socialiteTokens()
    {
        return $this->morphMany(SocialiteAccessToken::class, 'tokenable');
    }

    /**
     * Determine if the current access token has a given scope.
     *
     * @param  string  $scope
     *
     * @return bool
     */
    public function accessTokenHasScope(string $scope): bool
    {
        return $this->socialiteToken && $this->socialiteToken->hasScope($scope);
    }

    /**
     * Get the access token currently associated with the user.
     */
    public function currentSocialiteToken(): ?SocialiteAccessToken
    {
        return $this->socialiteToken;
    }

    /**
     * Set the current access token for the user.
     */
    public function withSocialiteToken(SocialiteAccessToken $accessToken): SocialiteAccessToken
    {
        $this->socialiteToken = $accessToken;

        return $this;
    }
}
