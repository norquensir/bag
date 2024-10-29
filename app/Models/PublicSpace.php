<?php

namespace App\Models;

use App\Traits\HasIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicSpace extends Model
{
    use HasIdentifier;

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
