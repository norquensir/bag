<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ViewController extends Controller
{
    public function postals()
    {
        return response()->download(Storage::disk('public')->path('adressen.csv'));
    }
}
