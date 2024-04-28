<?php

namespace SocialiteProviders\Zenit\Tokens;

use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read Collection|SocialiteAccessToken[] $socialiteTokens
 */
interface CarriesSocialiteTokens
{
    /**
     * Get the access tokens that belong to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function socialiteTokens();

    /**
     * Determine if the current access token has a given scope.
     *
     * @param  string  $scope
     *
     * @return bool
     */
    public function accessTokenHasScope(string $scope): bool;

    /**
     * Get the access token currently associated with the user.
     */
    public function currentSocialiteToken(): ?SocialiteAccessToken;

    /**
     * Set the current access token for the user.
     */
    public function withSocialiteToken(SocialiteAccessToken $accessToken): SocialiteAccessToken;
}
