<?php

namespace Norquensir\Bag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Norquensir\Bag\Traits\HasIdentifier;

class Place extends Model
{
    use HasFactory, HasIdentifier;

    protected $connection = 'bag';

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function publicSpaces(): HasMany
    {
        return $this->hasMany(PublicSpace::class);
    }
}
