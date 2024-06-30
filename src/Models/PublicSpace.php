<?php

namespace Norquensir\Bag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Norquensir\Bag\Traits\HasIdentifier;

class PublicSpace extends Model
{
    use HasFactory, HasIdentifier;

    protected $connection = 'bag';

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
