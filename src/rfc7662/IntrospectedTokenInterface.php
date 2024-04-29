<?php

namespace SocialiteProviders\Zenit\rfc7662;

use Laravel\Sanctum\Contracts\HasAbilities;

/**
 * Introspection Response.
 */
interface IntrospectedTokenInterface extends HasAbilities
{
    /**
     * Boolean indicator of whether or not the presented token
     * is currently active.  The specifics of a token's "active" state
     * will vary depending on the implementation of the authorization
     * server and the information it keeps about its tokens, but a "true"
     * value return for the "active" property will generally indicate
     * that a given token has been issued by this authorization server,
     * has not been revoked by the resource owner, and is within its
     * given time window of validity (e.g., after its issuance time and
     * before its expiration time).
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * A JSON string containing a space-separated list of
     * scopes associated with this token, in the format described in
     * Section 3.3 of OAuth 2.0 [RFC6749].
     */
    public function scope(): ?string;

    /**
     * Client identifier for the OAuth 2.0 client that
     * requested this token.
     */
    public function clientId(): ?string;

    /**
     * Human-readable identifier for the resource owner who
     * authorized this token.
     */
    public function username(): ?string;

    /**
     * Type of the token as defined in Section 5.1 of OAuth
     * 2.0 [RFC6749].
     */
    public function tokenType(): ?string;

    /**
     * Integer timestamp, measured in the number of seconds
     * since January 1 1970 UTC, indicating when this token will expire,
     * as defined in JWT [RFC7519].
     */
    public function exp(): ?int;

    /**
     * Integer timestamp, measured in the number of seconds
     * since January 1 1970 UTC, indicating when this token was
     * originally issued, as defined in JWT [RFC7519].
     */
    public function iat(): ?int;

    /**
     * Integer timestamp, measured in the number of seconds
     * since January 1 1970 UTC, indicating when this token is not to be
     * used before, as defined in JWT [RFC7519].
     */
    public function nbf(): ?int;

    /**
     * Subject of the token, as defined in JWT [RFC7519].
     * Usually a machine-readable identifier of the resource owner who
     * authorized this token.
     */
    public function sub(): ?string;

    /**
     * Service-specific string identifier or list of string
     * identifiers representing the intended audience for this token, as
     * defined in JWT [RFC7519].
     */
    public function aud(): ?string;

    /**
     * String representing the issuer of this token, as
     * defined in JWT [RFC7519].
     */
    public function iss(): ?string;

    /**
     * String identifier for the token, as defined in JWT
     * [RFC7519].
     */
    public function jti(): ?string;
}