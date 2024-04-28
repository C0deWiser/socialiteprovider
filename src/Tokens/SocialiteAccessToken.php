<?php

namespace SocialiteProviders\Zenit\Tokens;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property integer $id
 * @property string $provider
 * @property string $access_token
 * @property null|string $refresh_token
 * @property null|array $scopes
 * @property null|Carbon $last_used_at
 * @property null|Carbon $expires_at
 */
class SocialiteAccessToken extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'scopes'       => 'array',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'access_token',
        'refresh_token',
        'scopes',
        'expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }

    /**
     * Determine if the token carries a given scope.
     *
     * @param  string  $scope
     *
     * @return bool
     */
    public function hasScope(string $scope): bool
    {
        return array_key_exists($scope, array_flip($this->scopes));
    }

    /**
     * Determine if the token is missing a given scope.
     *
     * @param  string  $scope
     *
     * @return bool
     */
    public function doesntHaveScope(string $scope): bool
    {
        return !$this->hasScope($scope);
    }
}
