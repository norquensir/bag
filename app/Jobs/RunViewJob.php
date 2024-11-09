<?php

namespace App\Jobs;

use App\Models\Address;
use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Saloon\XmlWrangler\XmlReader;
use ZipArchive;

class RunViewJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('csv');
    }

    public function handle(): void
    {
        foreach (File::query()->where('extension', 'zip')->whereNull('type')->get() as $file) {
            $zip = new ZipArchive();
            if ($zip->open(Storage::path($file->path)) === true) {
                $date = XmlReader::fromString($zip->getFromName('Leveringsdocument-BAG-Extract.xml'))
                    ->value('selecties-extract:StandTechnischeDatum')
                    ->first();

                $zip->close();
            }

            if (!empty($date)) {
                $fileName = join('_', [Carbon::parse($date)->format('mY'), 'adressen.csv']);

                if (File::query()->where('path', $fileName)->doesntExist()) {
                    $rows = Address::query()->select('postal')->distinct()->get();
                    $file = fopen(Storage::disk('public')->path($fileName), 'w');

                    fputcsv($file, ['Postcode', 'Straatnaam', 'Plaatsnaam']);

                    foreach ($rows as $row) {
                        $address = Address::query()->with('publicSpace.place')->where('postal', $row->postal)->first();

                        fputcsv($file, [$address->postal, $address->publicSpace->name, $address->publicSpace->place->name]);
                    }

                    fclose($file);

                    $file = new File();
                    $file->uuid = Str::uuid();
                    $file->path = $fileName;
                    $file->extension = 'csv';
                    $file->type = null;
                    $file->save();
                }
            }
        }
    }
}
