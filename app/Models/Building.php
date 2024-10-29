<?php

namespace App\Models;

use App\Traits\HasIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Building extends Model
{
    use HasIdentifier;

    public function residentialObjects(): BelongsToMany
    {
        return $this->belongsToMany(ResidentialObject::class);
    }
}
