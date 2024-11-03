<?php

namespace App\Console\Commands;

use App\Jobs\RunDownloadJob;
use App\Jobs\RunZipJob;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\error;

class RunJobCommand extends Command
{
    protected $signature = 'run:job';

    protected $description = 'Run a job from command line';

    public function handle()
    {
        $allowedJobs = [
            'RunDownloadJob.php',
            'RunZipJob.php',
        ];

        $chosenJob = select(
            label: 'What job do you want to run?',
            options: $allowedJobs,
        );

        switch ($chosenJob) {
            case 'RunDownloadJob.php':
                RunDownloadJob::dispatch();
                break;

                case 'RunZipJob.php':
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
                    $runOnce = confirm('Do you want to run this job once?');

                    RunZipJob::dispatch($type, $runOnce);
                    break;

            default:
                error('Invalid job');
        }
    }
}
