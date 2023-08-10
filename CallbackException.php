<?php

namespace SocialiteProviders\Zenit;

class CallbackException extends \InvalidArgumentException
{
    protected $error;

    public function __construct($error_description = "", $error = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($error_description, $code, $previous);

        $this->error = $error;
    }

    public function getError(): string
    {
        return $this->error;
    }
}