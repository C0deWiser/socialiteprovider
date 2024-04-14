<?php

namespace SocialiteProviders\Zenit\Middlewares;

use Closure;
use Illuminate\Http\Request;
use SocialiteProviders\Zenit\Auth\Bearer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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

        if ($user instanceof Bearer) {
            $bearerScopes = explode(' ', $user->getIntrospectedToken()->scope());

            if (array_intersect($scopes, $bearerScopes)) {
                return $next($request);
            }
        }

        $requiredScopes = implode(' ', $scopes);

        throw new UnauthorizedHttpException(
            'Bearer realm="resource", scope="'.$requiredScopes.'", error="insufficient_scope"',
            "One of scopes required: ".$requiredScopes
        );
    }
}
