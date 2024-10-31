<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ViewController extends Controller
{
    public function postals()
    {
        $rows = Address::query()->select('postal')->distinct()->get();
        $postals = [];

        $file = fopen(Storage::disk('public')->path('test.csv'), 'w');

        fputcsv($file, ['Postcode', 'Straatnaam', 'Plaatsnaam']);

        foreach ($rows as $row) {
            $address = Address::query()->with('publicSpace.place')->where('postal', $row->postal)->first();

            fputcsv($file, [$address->postal, $address->publicSpace->name, $address->publicSpace->place->name]);
        }

        fclose($file);

        return response('Success', 200);
    }
}
