<?php

namespace SocialiteProviders\Zenit;

abstract class OAuth2Exception extends \Exception
{
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