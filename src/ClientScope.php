<?php

namespace SocialiteProviders\Zenit;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

/**
 * @property string $name
 * @property string $description
 * @property string $realm
 * @property array $aud
 * @property null|boolean $deprecated
 */
class ClientScope extends Pivot implements Castable
{
    protected $casts = [
        'aud'        => 'array',
        'deprecated' => 'boolean',
    ];

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {

            public function get(Model $model, string $key, $value, array $attributes): Collection
            {
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }
                if (is_array($value)) {
                    $value = array_map(fn($item) => new ClientScope($item), $value);
                }
                return collect(is_array($value) ? $value : []);
            }

            public function set(Model $model, string $key, $value, array $attributes)
            {
                if ($value instanceof Arrayable) {
                    $value = $value->toArray();
                }
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                return $value;
            }
        };
    }
}