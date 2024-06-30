<?php

namespace Norquensir\Bag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressName extends Model
{
    use HasFactory;

    protected $connection = 'bag';

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}