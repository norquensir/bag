<?php

namespace Norquensir\Bag\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Norquensir\Bag\Models\File;

class RunDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $downloadUrl = 'https://service.pdok.nl/kadaster/adressen/atom/v1_0/downloads/lvbag-extract-nl.zip';
            $directoryName = Str::uuid();

            if (!Storage::disk('bag')->exists($directoryName)) {
                Storage::disk('bag')->makeDirectory($directoryName);
            }

            $filePath = Str::of('/:directory:/:name:.zip')
                ->replace(':directory:', $directoryName)
                ->replace(':name:', Str::random(40));

            $client = new Client;

            $client->get($downloadUrl, [
                'sink' => Storage::disk('bag')->path($filePath),
            ]);

            $file = new File;
            $file->uuid = Str::uuid();
            $file->path = $filePath;
            $file->extension = 'zip';
            $file->save();
        });

        if (App::isProduction()) {
            RunZipJob::dispatch('WPL');
        }
    }
}
