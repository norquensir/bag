<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __invoke(Request $request, File $file)
    {
        Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/private/_downloads'),
        ])->put(join('_', [Carbon::now()->format('dmY'), Carbon::now()->format('His')]) . '.txt', $request);

        return response()->download(Storage::disk('public')->path($file->path));
    }
}
