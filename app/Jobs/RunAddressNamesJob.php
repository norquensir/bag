<?php

namespace App\Jobs;

use App\Models\Address;
use App\Models\AddressName;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunAddressNamesJob implements ShouldQueue
{
    use Queueable;

    public string $type;

    public function __construct(string $type)
    {
        $this->type = $type;

        $this->onQueue('processing');
    }

    public function handle(): void
    {
        if ($this->type == 'create') {
            foreach (Address::query()->lazy() as $address) {
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
