<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use SocialiteProviders\Zenit\ClientConfiguration;

class ClientConfigurationTest extends TestCase
{
    public function test()
    {
        $response = '{
    "client_id": "41d71ecb-7d98-31f9-abe7-c55368611c48",
    "client_secret": "d92d95c2cb7c902201955fe68ebb8a7a5c1902bc635a65d5d239461bc72d9f3e",
    "client_id_issued_at": 1730364154,
    "client_secret_expires_at": 0,
    "redirect_uris": [
        "https://example.com/callback",
        "https://dev.local/callback"
    ],
    "grant_types": [
        "authorization_code",
        "password",
        "client_credentials",
        "refresh_token",
        "token_to_ott",
        "ott_to_token"
    ],
    "name": "My application name",
    "namespace": "foobar",
    "is_enabled": true,
    "scopes": [
        {
            "name": "test",
            "description": "Human-readable scope description",
            "aud": [
                "personal"
            ],
            "realm": "public",
            "deprecated": false
        }
    ]
}';
        $request = '{
    "name": "My application name",
    "namespace": "foobar",
    "redirect_uri": "https://dev.local/callback",
    "scopes": [
        {
            "name": "test",
            "description": "Human-readable scope description",
            "aud": ["personal"],
            "realm": "public",
            "deprecated": false
        }
    ]
}';

        $response = json_decode($response, true);
        $conf = new ClientConfiguration($response);

        $this->assertEquals($response, $conf->toArray());

        $conf->redirect_uri = 'https://dev.local/callback';
        $request = json_decode($request, true);
        $this->assertEquals($request, $conf->toUpdateArray());
    }
}
