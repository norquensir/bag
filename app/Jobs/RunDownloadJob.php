<?php

namespace App\Jobs;

use App\Models\File;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Saloon\XmlWrangler\XmlReader;
use ZipArchive;

class RunDownloadJob implements ShouldQueue
{
    use Queueable;

    public bool $runZip = false;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $downloadUrl = 'https://service.pdok.nl/kadaster/adressen/atom/v1_0/downloads/lvbag-extract-nl.zip';
            $fileUuid = Str::uuid();

            if (!Storage::exists($fileUuid)) {
                Storage::makeDirectory($fileUuid);
            }

            $filePath = join('/', [$fileUuid, 'lvbag-extract-nl.zip']);
            $client = new Client;

            $client->get($downloadUrl, [
                'sink' => Storage::path($filePath),
            ]);

            $zip = new ZipArchive();
            if ($zip->open(Storage::path($filePath)) === true) {
                $date = XmlReader::fromString($zip->getFromName('Leveringsdocument-BAG-Extract.xml'))
                    ->value('selecties-extract:StandTechnischeDatum')
                    ->first();

                $zip->close();
            }

            if (empty($date)) {
                Storage::deleteDirectory($fileUuid);

                throw new Exception('Date is empty');
            } elseif (Carbon::parse($date)->isLastMonth()) {
                Storage::deleteDirectory($fileUuid);

                self::dispatch()->delay(Carbon::now()->addHour());
            } else {
                $file = new File;
                $file->uuid = $fileUuid;
                $file->path = $filePath;
                $file->extension = 'zip';
                $file->save();

                $this->runZip = true;
            }
        });

        if ($this->runZip && App::isProduction()) {
            RunZipJob::dispatch('WPL');
        }
    }
}
