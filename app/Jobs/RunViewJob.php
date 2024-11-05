<?php

namespace App\Jobs;

use App\Models\Address;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class RunViewJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('csv');
    }

    public function handle(): void
    {
        $rows = Address::query()->select('postal')->distinct()->get();
        $file = fopen(Storage::disk('public')->path(join('_', [Carbon::today()->format('mY'), 'adressen.csv'])), 'w');

        fputcsv($file, ['Postcode', 'Straatnaam', 'Plaatsnaam']);

        foreach ($rows as $row) {
            $address = Address::query()->with('publicSpace.place')->where('postal', $row->postal)->first();

            fputcsv($file, [$address->postal, $address->publicSpace->name, $address->publicSpace->place->name]);
        }

        fclose($file);
    }
}
