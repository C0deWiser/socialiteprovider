# Zenit

```bash
composer require codewiser/socialiteprovider
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'zenit' => [    
  'base_uri' => env('ZENIT_SERVER'),  
  'client_id' => env('ZENIT_CLIENT_ID'),  
  'client_secret' => env('ZENIT_CLIENT_SECRET'),  
  'redirect' => env('ZENIT_REDIRECT_URI') 
],
```

### Add provider event listener

Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\Zenit\ZenitExtendSocialite::class,
    ],
];
```

### Usage

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('zenit')->redirect();
```

### Returned User fields

- ``id``
- ``nickname``
- ``name``
- ``email``
- ``avatar``

### Access Token

Access Token is now an object, not just a string.

```php
$user = Socialite::driver('zenit')->user();

$token = $user->token;

// \League\OAuth2\Client\Token\AccessToken
```

### Error Response

Package provides response error handling compliant to rfc6749.

```php

try {

    $user = Socialite::driver('zenit')->user();

} catch (OAuth2Exception $e) {

    return match ($e->getError()) {

        // Show response to the user
        'access_denied',
        'server_error',
        'temporarily_unavailable' =>

            redirect()
                ->to(route('login'))
                ->with('error', $e->getMessage()),

        // Silently
        'interaction_required' => redirect()->to('/'),

        // Unrecoverable
        default => throw $e,
    };
}
```

### Token Introspection

Package provides token introspection compliant to rfc7662.

```php
use \Illuminate\Http\Request;
use \SocialiteProviders\Zenit\rfc7662\IntrospectedTokenInterface;

public function api(Request $request) {
    
    /** @var IntrospectedTokenInterface $token */
    $token = Socialite::driver('zenit')
        ->introspectToken($request->bearerToken());
    
    if ($token->isActive()) {
        //  
    }
}
```

### Refreshing Token

```php
$token = Socialite::driver('zenit')
            ->grantRefresh($refresh_token);
```

### Client Token

```php
$token = Socialite::driver('zenit')
            ->grantClientCredentials('scope-1 scope-2');
```

### Token by username and password

```php
$token = Socialite::driver('zenit')
            ->grantPassword($username, $password, 'scope-1 scope-2');
```

### Token by custom grant

```php
$token = Socialite::driver('zenit')
            ->grant('custom_grant', [/* any request params */]);
```