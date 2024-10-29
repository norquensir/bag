<?php

namespace App\Http\Controllers;

use App\Models\BoatSpot;
use App\Models\ResidentialObject;
use App\Models\TrailerSpot;
use Illuminate\Http\Request;

class IdentifierController extends Controller
{
    public function __invoke(Request $request, string $identifier)
    {
        if (preg_match('/^[0-9]{4}01[0-9]{10}$/', $identifier)) {
            $residentialObject = ResidentialObject::query()
                ->with([
                    'address.publicSpace.place',
                    'buildings',
                ])
                ->where('identifier', $identifier)
                ->orderByDesc('id')
                ->first();

            if ($residentialObject) {
                return response([
                    'postal' => $residentialObject->address->postal,
                    'street_name' => $residentialObject->address->publicSpace->name,
                    'street_number' => $residentialObject->address->street_number,
                    'street_number_ext' => $residentialObject->address->street_number_ext,
                    'street_number_add' => $residentialObject->address->street_number_add,
                    'city' => $residentialObject->address->publicSpace->place->name,
                    'building' => [
                        'surface' => null,
                        'construction_year' => null,
                        'latitude' => $residentialObject->latitude,
                        'longitude' => $residentialObject->longitude,
                        'polygons' => $residentialObject->buildings()->first()->polygons,
                        'bag_building_id' => $residentialObject->buildings()->first()->identifier,
                        'bag_data' => null,
                    ],
                ]);
            }
        } elseif (preg_match('/^[0-9]{4}02[0-9]{10}$/', $identifier)) {
            $boatSpot = BoatSpot::query()
                ->with('address.publicSpace.place')
                ->where('identifier', $identifier)
                ->orderByDesc('id')
                ->first();

            if ($boatSpot) {
                return response([
                    'postal' => $boatSpot->address->postal,
                    'street_name' => $boatSpot->address->publicSpace->name,
                    'street_number' => $boatSpot->address->street_number,
                    'street_number_ext' => $boatSpot->address->street_number_ext,
                    'street_number_add' => $boatSpot->address->street_number_add,
                    'city' => $boatSpot->address->publicSpace->place->name,
                    'building' => [
                        'surface' => null,
                        'construction_year' => null,
                        'latitude' => $boatSpot->latitude,
                        'longitude' => $boatSpot->longitude,
                        'polygons' => $boatSpot->polygons,
                        'bag_building_id' => null,
                        'bag_data' => null,
                    ],
                ]);
            }
        } elseif (preg_match('/^[0-9]{4}03[0-9]{10}$/', $identifier)) {
            $trailerSpot = TrailerSpot::query()
                ->with('address.publicSpace.place')
                ->where('identifier', $identifier)
                ->orderByDesc('id')
                ->first();

            if ($trailerSpot) {
                return response([
                    'postal' => $trailerSpot->address->postal,
                    'street_name' => $trailerSpot->address->publicSpace->name,
                    'street_number' => $trailerSpot->address->street_number,
                    'street_number_ext' => $trailerSpot->address->street_number_ext,
                    'street_number_add' => $trailerSpot->address->street_number_add,
                    'city' => $trailerSpot->address->publicSpace->place->name,
                    'building' => [
                        'surface' => null,
                        'construction_year' => null,
                        'latitude' => $trailerSpot->latitude,
                        'longitude' => $trailerSpot->longitude,
                        'polygons' => $trailerSpot->polygons,
                        'bag_building_id' => null,
                        'bag_data' => null,
                    ],
                ]);
            }
        } else {
            return response([
                'message' => 'Unsupported BAG ID',
            ], 400);
        }

        return response([
            'message' => 'BAG ID not found',
        ], 404);
    }
}
