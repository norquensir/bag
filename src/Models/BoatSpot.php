<?php

namespace Norquensir\Bag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Norquensir\Bag\Traits\HasIdentifier;

class BoatSpot extends Model
{
    use HasFactory, HasIdentifier;

    protected $connection = 'bag';

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class);
    }
}
