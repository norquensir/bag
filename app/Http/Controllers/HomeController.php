<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        Carbon::setlocale('nl');

        return view('home')->with([
            'files' => File::query()->where('extension', 'csv')->latest()->get(),
        ]);
    }
}
