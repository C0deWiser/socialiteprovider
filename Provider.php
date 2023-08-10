<?php

namespace SocialiteProviders\Zenit;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

/**
 * @method bool hasInvalidState()
 */
class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'ZENIT';

    public static function additionalConfigKeys(): array
    {
        return ['base_uri'];
    }

    public function __call($name, $arguments)
    {
        // Make it public?
        if ($name == 'hasInvalidState') {
            return $this->hasInvalidState();
        }
    }

    protected function getBaseUri(): string
    {
        return trim($this->getConfig('base_uri'), '/');
    }

    protected function buildPath(string $path): string
    {
        return $this->getBaseUri() . '/' . ltrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            $this->buildPath('auth'),
            $state
        );
    }

    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        if ($this->request->has('error')) {

            $error = $this->request->get('error');
            $error_description = $this->request->get('error_description') ?? $error;

            throw new CallbackException($error_description, $error);
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        $this->user = $this->mapUserToObject($this->getUserByToken(
            $token = Arr::get($response, 'access_token')
        ));

        return $this->user->setToken($token)
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'))
            ->setApprovedScopes(explode($this->scopeSeparator, Arr::get($response, 'scope', '')));
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->buildPath('oauth/token');
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->buildPath('api/user'),
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => Arr::get($user, 'data.id'),
            'nickname' => Arr::get($user, 'data.email'),
            'name'     => trim(Arr::get($user, 'data.first_name') . ' ' . Arr::get($user, 'data.family_name')),
            'email'    => Arr::get($user, 'data.email'),
            'avatar'   => Arr::get($user, 'data.picture'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code): array
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}