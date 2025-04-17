<?php

namespace SocialiteProviders\Zenit;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\InvalidStateException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Zenit\Exceptions\OAuth2CallbackException;
use SocialiteProviders\Zenit\Exceptions\OAuth2Exception;
use SocialiteProviders\Zenit\Exceptions\OAuth2TokenException;
use SocialiteProviders\Zenit\rfc6749\GrantAuthorizationCodeContract;
use SocialiteProviders\Zenit\rfc6749\GrantClientCredentialsContract;
use SocialiteProviders\Zenit\rfc6749\GrantPasswordContract;
use SocialiteProviders\Zenit\rfc6749\GrantRefreshContract;
use SocialiteProviders\Zenit\rfc7592\ClientManageContract;
use SocialiteProviders\Zenit\rfc7662\IntrospectedTokenInterface;
use SocialiteProviders\Zenit\rfc7662\TokenIntrospectionInterface;

class ZenitProvider extends AbstractProvider implements
    GrantAuthorizationCodeContract,
    GrantClientCredentialsContract,
    GrantPasswordContract,
    GrantRefreshContract,
    TokenIntrospectionInterface,
    ClientManageContract
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'ZENIT';

    public static function additionalConfigKeys(): array
    {
        return [
            'base_uri',
            'auth_endpoint',
            'token_endpoint',
            'user_endpoint',
            'token_introspect_endpoint',
            'client_manage_endpoint',
        ];
    }

    protected function getBaseUri(): string
    {
        return trim($this->getConfig('base_uri'), '/');
    }

    protected function buildPath(string $path): string
    {
        return $this->getBaseUri().'/'.ltrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            $this->buildPath($this->getConfig('auth_endpoint', 'auth')),
            $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->buildPath($this->getConfig('token_endpoint', 'oauth/token'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->buildPath($this->getConfig('user_endpoint', 'api/user')), [
            RequestOptions::HEADERS => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ]
        ]);

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
            'name'     => trim(Arr::get($user, 'data.first_name').' '.Arr::get($user, 'data.family_name')),
            'email'    => Arr::get($user, 'data.email'),
            'avatar'   => Arr::get($user, 'data.picture'),
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws OAuth2Exception
     * @throws GuzzleException
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

        $token = $this->grantAuthorizationCode($this->getCode(), $this->redirectUrl);

        $this->user = $this->userFromToken($token);

        return $this->user;
    }

    public function userFromToken($token)
    {
        $user =  parent::userFromToken($token);

        if ($token instanceof AccessToken) {
            $user
                ->setRefreshToken($token->getRefreshToken())
                ->setExpiresIn($token->getExpires() - $token->getTimeNow())
                ->setApprovedScopes(explode($this->scopeSeparator, Arr::get($token->getValues(), 'scope', '')));
        }

        return $user;
    }

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function introspectToken(string $token): IntrospectedTokenInterface
    {
        try {
            $response = $this->getHttpClient()->post(
                $this->buildPath($this->getConfig('token_introspect_endpoint', 'token_info')), [
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
    public function examineCallbackResponse()
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

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function grant(string $grant_type, array $request): AccessTokenInterface
    {
        $request = $request +
            [
                'grant_type'    => $grant_type,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ];

        try {
            $response = $this->getHttpClient()->post(
                $this->getTokenUrl(), [
                RequestOptions::HEADERS     => [
                    'Accept' => 'application/json'
                ],
                RequestOptions::FORM_PARAMS => $request,
            ]);

            $response = json_decode($response->getBody()->getContents(), true);

            return new AccessToken($response);

        } catch (ClientException $e) {
            $this->examineTokenResponse($e);
        }
    }

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function grantClientCredentials(string $scope = ''): AccessTokenInterface
    {
        return $this->grant('client_credentials', [
            'scope' => $scope
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function grantPassword(string $username, string $password, string $scope = ''): AccessTokenInterface
    {
        return $this->grant('password', [
            'username' => $username,
            'password' => $password,
            'scope'    => $scope
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function grantRefresh(string $refresh_token): AccessTokenInterface
    {
        return $this->grant('refresh_token', [
            'refresh_token' => $refresh_token,
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function grantAuthorizationCode(string $code, string $redirect_uri): AccessToken
    {
        return $this->grant('authorization_code', [
            'code'         => $code,
            'redirect_uri' => $redirect_uri
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function getClientConfiguration(): array
    {
        try {
            $response = $this->getHttpClient()->get(
                $this->buildPath($this->getConfig('client_manage_endpoint', 'oauth/client')), [
                RequestOptions::HEADERS => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret),
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $this->examineTokenResponse($e);
        }
    }

    /**
     * @throws GuzzleException
     * @throws OAuth2TokenException
     */
    public function updateClientConfiguration(array $config): array
    {
        try {
            $response = $this->getHttpClient()->put(
                $this->buildPath($this->getConfig('client_manage_endpoint', 'oauth/client')), [
                RequestOptions::HEADERS     => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret),
                ],
                RequestOptions::FORM_PARAMS => $config,
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $this->examineTokenResponse($e);
        }
    }
}