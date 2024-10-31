<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ViewController extends Controller
{
    public function postals()
    {
        $rows = Address::query()->select('postal')->distinct()->limit(100)->get();
        $postals = [];

        foreach ($rows as $row) {
            $postal = Address::query()->with('publicSpace.place')->where('postal', $row->postal)->first();

            $postals[] = [
                'postal' => $postal->postal,
                'street_name' => $postal->publicSpace->name,
                'city' => $postal->publicSpace->place->name,
            ];
        }

        $file = fopen(Storage::disk('public')->path('test.csv'), 'w');

        fputcsv($file, ['Postcode', 'Straatnaam', 'Plaatsnaam']);

        foreach ($postals as $postal) {
            fputcsv($file, [$postal['postal'], $postal['street_name'], $postal['city']]);
        }

        fclose($file);

        return response('Success', 200);
    }
}
