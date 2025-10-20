<?php

namespace SocialiteProviders\Zenit;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property-read string $client_id
 * @property-read string $client_secret
 * @property-read Carbon $client_id_issued_at
 * @property-read integer $client_secret_expires_at
 * @property-read array $redirect_uris
 * @property-read array $grant_types
 * @property-read boolean $is_enabled
 *
 * @property string $name
 * @property string $namespace
 * @property Collection<integer, ClientScope> $scopes
 * @property-write string $redirect_uri
 */
class ClientConfiguration extends Pivot
{
    protected $casts = [
        'client_id_issued_at' => 'timestamp',
        'redirect_uris'       => 'array',
        'grant_types'         => 'array',
        'is_enabled'          => 'boolean',
        'scopes'              => ClientScope::class,
    ];

    public function toUpdateArray(): array
    {
        return [
            'name'         => $this->name,
            'namespace'    => $this->namespace,
            'redirect_uri' => $this->redirect_uri,
            'scopes'       => $this->scopes->toArray(),
        ];
    }
}