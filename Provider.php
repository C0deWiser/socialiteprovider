<?php

namespace SocialiteProviders\Zenit;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Zenit\rfc7662\IntrospectedTokenInterface;
use SocialiteProviders\Zenit\rfc7662\TokenIntrospectionInterface;

class Provider extends AbstractProvider implements TokenIntrospectionInterface
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'ZENIT';

    public static function additionalConfigKeys(): array
    {
        return ['base_uri'];
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
                RequestOptions::HEADERS => [
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

    /**
     * {@inheritdoc}
     * @throws OAuth2Exception
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $this->examineCallbackResponse();

        try {
            $response = $this->getAccessTokenResponse($this->getCode());
        } catch (ClientException $e) {
            $this->examineTokenResponse($e);
        }

        $this->user = $this->mapUserToObject($this->getUserByToken(
            $token = Arr::get($response, 'access_token')
        ));

        return $this->user->setToken($token)
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'))
            ->setApprovedScopes(explode($this->scopeSeparator, Arr::get($response, 'scope', '')));
    }

    public function introspectToken(string $token): IntrospectedTokenInterface
    {
        try {

            $response = $this->getHttpClient()->post(
                $this->buildPath('token_info'),
                [
                    RequestOptions::HEADERS     => [
                        'Accept' => 'application/json'
                    ],
                    RequestOptions::FORM_PARAMS => [
                        'client_id'     => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'token'         => $token
                    ],
                ]);

            $response = json_decode($response->getBody()->getContents(), true);

            return new IntrospectedToken($response);

        } catch (ClientException $e) {
            $this->examineTokenResponse($e);
        }
    }


    /**
     * @throws OAuth2CallbackException
     */
    protected function examineCallbackResponse()
    {
        if ($this->request->has('error')) {
            throw new OAuth2CallbackException(
                $this->request->get('error'),
                $this->request->get('error_description', ''),
                $this->request->get('error_uri', '')
            );
        }
    }

    /**
     * @throws OAuth2TokenException
     */
    protected function examineTokenResponse(ClientException $e)
    {
        $response = json_decode($e->getResponse()->getBody()->getContents(), true);

        if (is_array($response) && isset($response['error'])) {
            throw new OAuth2TokenException(
                $response['error'],
                $response['error_description'] ?? '',
                $response['error_uri'] ?? '',
                $e->getResponse()->getStatusCode()
            );
        }

        throw $e;
    }
}