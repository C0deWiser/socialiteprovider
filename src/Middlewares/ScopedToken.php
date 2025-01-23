<?php

namespace SocialiteProviders\Zenit\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @deprecated Prefer https://laravel.com/docs/11.x/sanctum#token-ability-middleware
 */
class ScopedToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$scopes): Response
    {
        $user = $request->user();

        if ($user instanceof HasApiTokens) {
            foreach ($scopes as $scope) {
                if ($user->currentAccessToken()->can($scope)) {
                    return $next($request);
                }
            }
        }

        $insufficient_scopes = implode(' ', $scopes);

        throw new UnauthorizedHttpException(
            'Bearer realm="resource", scope="'.$insufficient_scopes.'", error="insufficient_scope"',
            "One of scopes required: ".$insufficient_scopes
        );
    }
}
