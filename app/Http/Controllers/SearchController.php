<?php

namespace App\Http\Controllers;

use App\Models\AddressName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'type' => 'required|in:name,full_street,full_address',
            'search' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validation->errors(),
            ], 422);
        }

        if ($request->get('type') == 'name') {
            $addressNameQuery = AddressName::query()->where('name', 'like', $request->get('search'));
        } else {
            $addressNameQuery = AddressName::query()
                ->whereFullText(
                    $request->get('type'),
                    Str::of('":search"')->replace(':search', $request->get('search'))->replace(' ', '%'),
                );
        }

        if ($addressNameQuery->count() == 1) {
            $address = $addressNameQuery->first()
                ->address()
                ->with(['publicSpace.place', 'residentialObject.buildings'])
                ->first();

            return response([
                'result' => [
                    'postal' => $address->postal,
                    'street_name' => $address->publicSpace->name,
                    'street_number' => $address->street_number,
                    'street_number_ext' => $address->street_number_ext,
                    'street_number_add' => $address->street_number_add,
                    'city' => $address->publicSpace->place->name,
                    'building' => [
                        'surface' => null,
                        'construction_year' => null,
                        'latitude' => $address->residentialObject->latitude,
                        'longitude' => $address->residentialObject->longitude,
                        'polygons' => $address->residentialObject->buildings()->first()->polygons,
                        'bag_building_id' => $address->residentialObject->buildings()->first()->identifier,
                        'bag_data' => null,
                    ],
                ],
            ]);
        } elseif ($addressNameQuery->count() > 1) {
            $results = [];
            foreach ($addressNameQuery->get() as $addressName) {
                $address = $addressName->address()
                    ->with(['publicSpace.place', 'residentialObject.buildings'])
                    ->first();

                $results[] = [
                    'postal' => $address->postal,
                    'street_name' => $address->publicSpace->name,
                    'street_number' => $address->street_number,
                    'street_number_ext' => $address->street_number_ext,
                    'street_number_add' => $address->street_number_add,
                    'city' => $address->publicSpace->place->name,
                    'building' => [
                        'surface' => null,
                        'construction_year' => null,
                        'latitude' => $address->residentialObject->latitude,
                        'longitude' => $address->residentialObject->longitude,
                        'polygons' => $address->residentialObject->buildings()->first()->polygons,
                        'bag_building_id' => $address->residentialObject->buildings()->first()->identifier,
                        'bag_data' => null,
                    ],
                ];
            }

            return response([
                'results' => $results,
            ]);
        }

        return response([
            'message' => 'No search results found',
        ], 404);
    }
}
