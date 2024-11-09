<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __invoke(Request $request, File $file)
    {
        return response()->download(Storage::disk('public')->path($file->path));
    }
}
