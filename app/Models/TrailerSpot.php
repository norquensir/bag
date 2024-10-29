<?php

namespace App\Models;

use App\Traits\HasIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrailerSpot extends Model
{
    use HasIdentifier;

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
