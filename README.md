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
  'redirect' => env('ZENIT_REDIRECT_URI'),
  // optional attributes (with default values):
  'auth_endpoint' => 'auth',
  'token_endpoint' => 'oauth/token',
  'user_endpoint' => 'api/user',
  'token_introspect_endpoint' => 'token_info',
  'client_manage_endpoint' => 'oauth/client',
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

## Token Introspection

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

## Get user using existing token

```php
$user = Socialite::driver('zenit')
            ->userFromToken($request->bearerToken());
```

## Refreshing Token

```php
$token = Socialite::driver('zenit')
            ->refreshToken($refresh_token);
```

## Client Token

```php
$token = Socialite::driver('zenit')
            ->grantClientCredentials('scope-1 scope-2');
```

## Token by username and password

```php
$token = Socialite::driver('zenit')
            ->grantPassword($username, $password, 'scope-1 scope-2');
```

## Token by custom grant

```php
$token = Socialite::driver('zenit')
            ->grant('custom_grant', [/* any request params */]);
```

## Manage client

Package provides remote client management compliant to rfc7592. It allows to 
read OAuth client properties...

```php
use SocialiteProviders\Zenit\ClientConfiguration;

$config = new ClientConfiguration(
    Socialite::driver('zenit')->getClientConfiguration()
);
```

...and update it programmatically:

```php
use SocialiteProviders\Zenit\ClientConfiguration;
use SocialiteProviders\Zenit\ClientScope;

$config = new ClientConfiguration();

$config->name = 'name';
$config->namespace = 'namespace';
$config->redirect_uri = 'https://example.com';

$scope = new ClientScope();

$scope->name = 'name';
$scope->description = 'description';
$scope->aud = ['personal'];
$scope->realm = 'public';

$config->scopes->add($scope)

// etc

Socialite::driver('zenit')->updateClientConfiguration($config->toUpdateArray());
```

## Token authorization

Register auth driver, that would authorize incoming requests with bearer 
tokens, issued by oauth server.

Provide a cache instance to store introspected tokens.

You may pass a callback to make additional checks with an authenticated user.

```php
use SocialiteProviders\Zenit\Auth\TokenAuthorization;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

Auth::viaRequest('access_token', new TokenAuthorization(
    'zenit', 
    Auth::createUserProvider(config('auth.guards.api.provider')),
    cache()->driver(),
    function(Authenticatable $user) {
        if ($user->trashed()) {
            throw new AccessDeniedHttpException('Account is disabled');
        }
    }
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

Usually, _access_token_ associated with a user, but 
`client_credentials` _access_token_ is not. Such _access_token_ associated 
with oauth client only. 

So, the `Authenticatable` may be 
as `App\User`, 
as `SocialiteProviders\Zenit\Auth\Client` class. 

We expect that `User` implements `Laravel\Sanctum\Contracts\HasApiTokens`, 
as `Client` implements it too. If so, the `Authenticatable` will be injected 
with incoming token (aka current access token).

Current access token implements `Laravel\Sanctum\Contracts\HasAbilities` 
interface, so you may inspect its scopes and abilities.

```php
use Illuminate\Http\Request;
use Laravel\Sanctum\Contracts\HasApiTokens;

public function index(Request $request) {
    // Check scope
    $request->user()->tokenCan('my-scope');
}
```

As current access token looks and behave like `Sanctum` token, we may 
protect routes with `Laravel\Sanctum\Http\Middleware\CheckAbilities` or 
`Laravel\Sanctum\Http\Middleware\CheckForAnyAbility` middlewares, as 
described in 
[official documentation](https://laravel.com/docs/12.x/sanctum#token-ability-middleware).

```php
Route::get('/orders', function () {
    // Token has both "check-status" and "place-orders" abilities...
})->middleware(['auth:api', 'abilities:check-status,place-orders']);
```
