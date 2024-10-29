<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressName extends Model
{
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
