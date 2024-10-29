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

    public int $timeout = 0;

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
                if ($zip->open(Storage::path($file->path)) === true) {
                    $tmpPath = Str::of(':current:/tmp')
                        ->replace(':current:', Str::beforeLast($file->path, '/'));

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $fileName = $zip->statIndex($i)['name'];

                        if (preg_match('/[0-9]{4}(' . $this->type . ')[0-9]{8}/', $fileName)) {
                            $zip->extractTo(Storage::path($tmpPath), $fileName);

                            $unzipPath = Str::of(':path:/:file:')
                                ->replace(':path:', $tmpPath)
                                ->replace(':file:', $fileName);
                        }
                    }
                }

                if ($zip->open(Storage::path($unzipPath)) === true) {
                    $tmpPath = Str::of(':current:/tmp')
                        ->replace(':current:', Str::beforeLast($file->path, '/'));
                    $extractFiles = collect();

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $fileName = $zip->statIndex($i)['name'];
                        $filePath = Str::of(':tmp:/:file:')
                            ->replace(':tmp:', $tmpPath)
                            ->replace(':file:', $fileName);

                        if (!preg_match('/[0-9]{4}[A-Z]{3}[0-9]{8}/', $fileName)) {
                            continue;
                        }

                        if (!Storage::fileExists($filePath)) {
                            $extractFiles->add($fileName);
                        }
                    }

                    if ($extractFiles->isNotEmpty()) {
                        $zip->extractTo(Storage::path($tmpPath), $extractFiles->toArray());

                        $extractFiles->map(function ($extractFile) use ($tmpPath) {
                            $extractFilePath = Str::of(':tmp:/:file:')
                                ->replace(':tmp:', $tmpPath)
                                ->replace(':file:', $extractFile);

                            if (File::query()->where('path', $extractFilePath)->doesntExist()) {
                                $innerFile = new File;
                                $innerFile->uuid = Str::uuid();
                                $innerFile->path = $extractFilePath;
                                $innerFile->extension = 'xml';
                                $innerFile->type = $this->type;
                                $innerFile->save();
                            }
                        });
                    }
                }

                Storage::delete($unzipPath);
            }
        });

        $processFiles = File::query()
            ->where('extension', 'xml')
            ->where('type', $this->type)
            ->get();

        foreach ($processFiles as $processFile) {
            RunProcessJob::dispatch($processFile, $processFiles->last()->uuid == $processFile->uuid, $this->once);
        }
    }
}
