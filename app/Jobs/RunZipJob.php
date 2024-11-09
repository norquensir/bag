<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class RunZipJob implements ShouldQueue
{
    use Queueable;

    public string $type;

    public bool $once;

    public function __construct($type, $once = false)
    {
        $this->type = $type;
        $this->once = $once;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $file = File::query()->where('extension', 'zip')->latest()->first();
            $zip = new ZipArchive;
            $unzipPath = null;

            if ($file) {
                $tmpPath = join('/', [$file->uuid, 'tmp']);

                if ($zip->open(Storage::path($file->path)) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $fileName = $zip->statIndex($i)['name'];

                        if (preg_match('/[0-9]{4}(' . $this->type . ')[0-9]{8}/', $fileName)) {
                            $zip->extractTo(Storage::path($tmpPath), $fileName);

                            $unzipPath = join('/', [$tmpPath, $fileName]);
                        }
                    }

                    $zip->close();
                }

                if ($zip->open(Storage::path($unzipPath)) === true) {
                    $extractFiles = collect();

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $fileName = $zip->statIndex($i)['name'];
                        $filePath = join('/', [$tmpPath, $fileName]);

                        if (!preg_match('/[0-9]{4}[A-Z]{3}[0-9]{8}/', $fileName)) {
                            continue;
                        }

                        if (!Storage::fileExists($filePath)) {
                            $extractFiles->add($fileName);
                        }
                    }

                    if ($extractFiles->isNotEmpty()) {
                        $zip->extractTo(Storage::path($tmpPath), $extractFiles->toArray());

                        foreach ($extractFiles as $extractFile) {
                            $extractFilePath = join('/', [$tmpPath, $extractFile]);

                            if (File::query()->where('path', $extractFilePath)->doesntExist()) {
                                $innerFile = new File;
                                $innerFile->uuid = Str::uuid();
                                $innerFile->path = $extractFilePath;
                                $innerFile->extension = 'xml';
                                $innerFile->type = $this->type;
                                $innerFile->save();
                            }
                        }
                    }

                    $zip->close();
                }

                Storage::delete($unzipPath);
            }
        });

        $processFiles = File::query()->where('extension', 'xml')->where('type', $this->type)->get();

        foreach ($processFiles as $processFile) {
            RunProcessJob::dispatch($processFile, $this->once, $processFiles->last()->uuid == $processFile->uuid);
        }
    }
}
