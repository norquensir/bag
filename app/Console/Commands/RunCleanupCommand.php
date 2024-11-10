<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Saloon\XmlWrangler\XmlReader;
use ZipArchive;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class RunCleanupCommand extends Command
{
    protected $signature = 'run:cleanup';

    protected $description = 'Remove the unused import files';

    public function handle()
    {
        $cleanup = select(
            label: 'Which import do you want to cleanup?',
            options: [
                'import',
                'setCorrectDownloadDate',
                'removeDuplicateDownloads',
            ],
        );

        $this->$cleanup();
    }

    public function import()
    {
        $files = [];
        foreach (File::query()->where('extension', 'zip')->get() as $file) {
            $files[$file->uuid] = $file->created_at->format('d-m-Y H:i:s');
        }

        $fileUuid = select(
            label: 'Which import do you want to cleanup?',
            options: $files,
        );

        $children = Storage::files(join('/', [$fileUuid, 'tmp']));
        if (!empty($children)) {
            progress(
                label: 'Running cleanup',
                steps: Storage::files(join('/', [$fileUuid, 'tmp'])),
                callback: function ($file, $progress) {
                    $progress->hint('Removing ' . $file);

                    File::query()->where('path', $file)->delete();
                    Storage::delete($file);
                },
            );

            info('Removed all files successfully');
        } else {
            warning('No files found to remove');
        }
    }

    public function setCorrectDownloadDate()
    {
        foreach (File::query()->where('extension', 'zip')->get() as $file) {
            $zip = new ZipArchive();
            if ($zip->open(Storage::path($file->path)) === true) {
                $date = XmlReader::fromString($zip->getFromName('Leveringsdocument-BAG-Extract.xml'))
                    ->value('selecties-extract:StandTechnischeDatum')
                    ->first();

                $file->created_at = Carbon::parse($date);

                if ($file->isDirty()) {
                    $file->save();
                }

                $zip->close();
            }
        }
    }

    public function removeDuplicateDownloads()
    {
        $groups = [];
        foreach (File::query()->where('extension', 'zip')->oldest()->get() as $file) {
            $groups[$file->created_at->format('d-m-Y')][] = $file;
        }

        foreach ($groups as $group) {
            $duplicates = collect($group);

            if ($duplicates->count() > 1) {
                $duplicates->pop();

                foreach ($duplicates->values() as $duplicate) {
                    Storage::deleteDirectory($duplicate->uuid);
                    $duplicate->delete();

                    info($duplicate->uuid . ' deleted');
                }
            }
        }
    }
}
