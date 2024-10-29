<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class RunJobCommand extends Command
{
    protected $signature = 'run:job';

    protected $description = 'Run a job from command line';

    public array $allowedJobs = [
        'RunDownloadJob.php',
        'RunZipJob.php',
    ];

    public function handle()
    {
        $jobFiles = [];

        $storage = Storage::build([
            'driver' => 'local',
            'root' => __DIR__ . '/../../Jobs',
        ]);

        foreach ($storage->files() as $storageFile) {
            if (in_array($storageFile, $this->allowedJobs)) {
                $jobFiles[] = $storageFile;
            }
        }

        $jobFile = select(
            label: 'What job do you want to run?',
            options: $jobFiles,
        );

        $job = Str::replace(':class:', Str::remove('.php', $jobFile), 'App\Jobs\:class:');

        if ($jobFile == 'RunZipJob.php') {
            $type = select(
                label: '',
                options: [
                    'WPL' => 'places',
                    'OPR' => 'public_spaces',
                    'NUM' => 'addresses',
                    'LIG' => 'boat_spots',
                    'STA' => 'trailer_spots',
                    'PND' => 'buildings',
                    'VBO' => 'residential_objects',
                ],
            );

            dispatch(new $job($type, true));
        } else {
            dispatch(new $job);
        }
    }
}
