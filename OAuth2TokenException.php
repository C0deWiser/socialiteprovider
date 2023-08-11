<?php

namespace SocialiteProviders\Zenit;

/**
 * Grant Request with Error Response.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-5.2
 */
class OAuth2TokenException extends OAuth2Exception
{
    protected function fallbackErrorDescription(string $error): string
    {
        switch ($error) {
            case self::invalid_request:
                return "The request is missing a required parameter, includes an unsupported parameter value (other than grant type), repeats a parameter, includes multiple credentials, utilizes more than one mechanism for authenticating the client, or is otherwise malformed.";
            case self::invalid_client:
                return "Client authentication failed (e.g., unknown client, no client authentication included, or unsupported authentication method).";
            case self::invalid_grant:
                return "The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client.";
            case self::unauthorized_client:
                return "The authenticated client is not authorized to use this authorization grant type.";
            case self::unsupported_grant_type:
                return "The authorization grant type is not supported by the authorization server.";
            case self::invalid_scope:
                return "The requested scope is invalid, unknown, malformed, or exceeds the scope granted by the resource owner.";
            default:
                return "Unknown error";
        }
    }

    protected function fallbackErrorUri(string $error): string
    {
        return "https://datatracker.ietf.org/doc/html/rfc6749#section-5.2";
    }
}