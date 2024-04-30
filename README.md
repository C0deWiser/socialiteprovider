# Zenit

```bash
composer require codewiser/socialiteprovider
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/),
then follow the provider specific instructions below.

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

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`.
See the [Base Installation Guide](https://socialiteproviders.com/usage/) for
detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\Zenit\ZenitExtendSocialite::class,
    ],
];
```

### Usage

You should now be able to use the provider like you would regularly use
Socialite (assuming you have the facade installed):

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

### Get user using existing token

```php
$user = Socialite::driver('zenit')
            ->userFromToken($request->bearerToken());
```

### Refreshing Token

```php
$token = Socialite::driver('zenit')
            ->refreshToken($refresh_token);
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

## Token authorization

Register auth driver, that authorizes incoming requests with bearer tokens,
issued by some OAuth 2.0 server.

Socialite provider should implement `TokenIntrospectionInterface`.

```php
use SocialiteProviders\Zenit\Auth\TokenAuthorization;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

Auth::viaRequest('access_token', new TokenAuthorization(
    socialiteProvider: 'zenit', 
    userProvider: Auth::createUserProvider(config('auth.guards.api.provider')),
    cache: cache()->driver()
));
```

Next, register driver for the guard:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'access_token',
        'provider' => 'users',
    ]
]
```

As access_token may not be associated with a user, the `Authenticatable`
object is a `Client` class. It exists only during request.

```php
use Illuminate\Http\Request;
use Laravel\Sanctum\Contracts\HasApiTokens;

public function index(Request $request) {
    $authenticated = $request->user();
    
    if ($authenticated instanceof HasApiTokens) {
        // Check scope
        $authenticated->currentAccessToken()->scope();
    }
}
```

Other hand, you may use the `ScopedToken` middleware to inspect token scopes:

```php
use Illuminate\Support\Facades\Route;
use SocialiteProviders\Zenit\Middlewares\ScopedToken;

Route::get('example', 'ctl')
    ->middleware([ScopedToken::class.':my-scope,foo,bar'])
```