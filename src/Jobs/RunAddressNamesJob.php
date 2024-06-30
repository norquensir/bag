<?php

namespace Norquensir\Bag\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Norquensir\Bag\Models\Address;

class RunAddressNamesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function handle(): void
    {
        if ($this->type == 'create') {
            foreach (Address::all() as $address) {
                if (AddressName::query()->where('address_id', $address->id)->doesntExist()) {
                    $addressName = new AddressName;
                    $addressName->name = $address->name;
                    $addressName->full_street = $address->full_street;
                    $addressName->full_address = $address->full_address;
                    $addressName->address_id = $address->id;
                    $addressName->save();
                }
            }
        } elseif ($this->type == 'delete') {
            foreach (AddressName::query()->whereNull('name')->whereNull('full_address')->get() as $addressName) {
                $addressName->delete();
            }
        } else {
            throw new \Exception('Invalid job type');
        }
    }
}