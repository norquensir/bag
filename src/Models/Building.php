<?php

namespace Norquensir\Bag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Norquensir\Bag\Traits\HasIdentifier;

class Building extends Model
{
    use HasFactory, HasIdentifier;

    protected $connection = 'bag';

    public function residentialObjects(): BelongsToMany
    {
        return $this->belongsToMany(ResidentialObject::class);
    }
}
