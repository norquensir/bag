<?php

namespace App\Console\Commands;

use App\Models\Address;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\spin;

class RunTestCommand extends Command
{
    protected $signature = 'run:test';

    protected $description = 'Run test command';

    public function handle()
    {
        $this->processing();
    }

    private function processing(): void
    {
        $rows = spin(
            callback: fn () => Address::query()->select('postal')->distinct()->get(),
            message: 'Fetching postals...',
        );

        $file = fopen(Storage::disk('public')->path('adressen.csv'), 'w');

        fputcsv($file, ['Postcode', 'Straatnaam', 'Plaatsnaam']);

        foreach ($rows as $row) {
            $this->info(Carbon::now()->format('His') . ' | Processsing postal ' . $row->postal);

            $address = Address::query()->with('publicSpace.place')->where('postal', $row->postal)->first();

            fputcsv($file, [$address->postal, $address->publicSpace->name, $address->publicSpace->place->name]);
        }

        fclose($file);
    }
}
