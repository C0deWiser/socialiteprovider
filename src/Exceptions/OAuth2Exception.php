<?php

namespace SocialiteProviders\Zenit\Exceptions;

abstract class OAuth2Exception extends \Exception
{
    const access_denied = 'access_denied';
    const invalid_client = 'invalid_client';
    const invalid_grant = 'invalid_grant';
    const invalid_request = 'invalid_request';
    const invalid_scope = 'invalid_scope';
    const server_error = 'server_error';
    const temporarily_unavailable = 'temporarily_unavailable';
    const unauthorized_client = 'unauthorized_client';
    const unsupported_response_type = 'unsupported_response_type';
    const unsupported_grant_type = 'unsupported_grant_type';

    protected $error;
    protected $error_uri;

    public function __construct($error, $error_description = "", $error_uri = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($error_description ?: $this->fallbackErrorDescription($error), $code, $previous);

        $this->error = $error;
        $this->error_uri = $error_uri ?: $this->fallbackErrorUri($error);
    }

    /**
     * Get rfc6749 default error description.
     */
    abstract protected function fallbackErrorDescription(string $error): string;

    /**
     * Get rfc6749 uri.
     */
    abstract protected function fallbackErrorUri(string $error): string;

    public function getError(): string
    {
        return $this->error;
    }

    public function getErrorUri(): string
    {
        return $this->error_uri;
    }
}