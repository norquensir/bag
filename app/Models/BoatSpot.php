<?php

namespace App\Models;

use App\Traits\HasIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BoatSpot extends Model
{
    use HasIdentifier;

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class);
    }
}
