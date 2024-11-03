<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
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
}
