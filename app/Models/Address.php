<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasIdentifier;

class Address extends Model
{
    use HasIdentifier;

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function publicSpace(): BelongsTo
    {
        return $this->belongsTo(PublicSpace::class);
    }

    public function residentialObject(): HasOne
    {
        return $this->hasOne(ResidentialObject::class);
    }

    public function boatSpots(): HasMany
    {
        return $this->hasMany(BoatSpot::class);
    }

    public function trailerSpots(): HasMany
    {
        return $this->hasMany(TrailerSpot::class);
    }

    public function addressName(): HasOne
    {
        return $this->hasOne(AddressName::class);
    }

    public function getNameAttribute(): string
    {
        return $this->full_street . ', ' . $this->publicSpace->place->name;
    }

    public function getFullStreetAttribute(): string
    {
        $fullStreet = $this->publicSpace->name;
        $fullStreet .= ' ' . $this->street_number;

        if (!empty($this->street_number_ext)) {
            $fullStreet .= $this->street_number_ext;
        }

        if (!empty($this->street_number_add)) {
            $fullStreet .= is_numeric($this->street_number_add) ? '-' . $this->street_number_add : ' ' . $this->street_number_add;
        }

        return trim($fullStreet);
    }

    public function getFullAddressAttribute(): string
    {
        return $this->full_street . ', ' . $this->postal . ' ' . $this->publicSpace->place->name;
    }
}
