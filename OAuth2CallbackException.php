<?php

namespace SocialiteProviders\Zenit;

/**
 * Authorization Request with Error Response.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.2.1
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.2.2.1
 */
class OAuth2CallbackException extends OAuth2Exception
{
    const access_denied = 'access_denied';
    const invalid_request = 'invalid_request';
    const invalid_scope = 'invalid_scope';
    const server_error = 'server_error';
    const temporarily_unavailable = 'temporarily_unavailable';
    const unauthorized_client = 'unauthorized_client';
    const unsupported_response_type = 'unsupported_response_type';

    protected function fallbackErrorDescription(string $error): string
    {
        switch ($error) {
            case self::unauthorized_client:
                return "The client is not authorized to request an authorization code using this method.";
            case self::access_denied:
                return "The resource owner or authorization server denied the request.";
            case self::invalid_request:
                return "The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.";
            case self::unsupported_response_type:
                return "The authorization server does not support obtaining an authorization code using this method.";
            case self::invalid_scope:
                return "The requested scope is invalid, unknown, or malformed.";
            case self::server_error:
                return "The authorization server encountered an unexpected condition that prevented it from fulfilling the request.";
            case self::temporarily_unavailable:
                return "The authorization server is currently unable to handle the request due to a temporary overloading or maintenance of the server.";
            default:
                return "Unknown error";
        }
    }

    protected function fallbackErrorUri(string $error): string
    {
        return "https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.2.1";
    }
}