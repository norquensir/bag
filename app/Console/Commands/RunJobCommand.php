<?php

namespace App\Console\Commands;

use App\Jobs\RunAddressNamesJob;
use App\Jobs\RunDownloadJob;
use App\Jobs\RunViewJob;
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
        $chosenJob = select(
            label: 'What job do you want to run?',
            options: [
                'RunDownloadJob',
                'RunZipJob',
                'RunAddressNamesJob',
                'RunViewJob',
            ],
        );

        switch ($chosenJob) {
            case 'RunDownloadJob':
                RunDownloadJob::dispatch();
                break;

            case 'RunZipJob':
                $type = select(
                    label: 'Which file do you want to unzip?',
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

            case 'RunAddressNamesJob':
                $type = select(
                    label: 'Which type do you want to run?',
                    options: [
                        'create',
                        'delete',
                    ],
                );

                RunAddressNamesJob::dispatch($type);
                break;

            case 'RunViewJob':
                RunViewJob::dispatch();
                break;

            default:
                error('Invalid job');
        }
    }
}
