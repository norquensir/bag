<?php

namespace App\Models;

use App\Traits\HasIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    use HasIdentifier;

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function publicSpaces(): HasMany
    {
        return $this->hasMany(PublicSpace::class);
    }
}
