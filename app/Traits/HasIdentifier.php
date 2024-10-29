<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasIdentifier
{
    public function getRouteKeyName(): string
    {
        return 'identifier';
    }

    public static function findByIdentifier($identifier): Model|null
    {
        return self::query()->where('identifier', $identifier)->first();
    }
}
