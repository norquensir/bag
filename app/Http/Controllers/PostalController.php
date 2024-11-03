<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostalController extends Controller
{
    public function __invoke(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'postal' => 'required|regex:/^[1-9]{1}[0-9]{3}[ ]{0,1}[a-zA-Z]{2}$/',
            'street_number' => 'required|numeric|min:1|max:99999',
            'street_number_ext' => 'regex:/^[a-zA-Z]{1}$/',
            'street_number_add' => 'regex:/^[0-9a-zA-Z]{1,4}$/',
        ]);

        if ($validation->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validation->errors(),
            ], 422);
        }

        $address = [];
        foreach (['postal', 'street_number', 'street_number_ext', 'street_number_add'] as $field) {
            if ($request->isNotFilled($field)) {
                continue;
            }

            if ($field == 'postal') {
                $address[$field] = Str::of($request->get('postal'))->replace(' ', '')->toString();
            } else {
                $address[$field] = $request->get($field);
            }
        }

        if (array_keys($address) == ['postal', 'street_number']) {
            $address = Address::query()
                ->with(['publicSpace.place', 'residentialObject.buildings'])
                ->where('postal', $address['postal'])
                ->where('street_number', $address['street_number'])
                ->whereNull('street_number_ext')
                ->whereNull('street_number_add')
                ->first();
        } elseif (array_keys($address) == ['postal', 'street_number', 'street_number_ext']) {
            $address = Address::query()
                ->with(['publicSpace.place', 'residentialObject.buildings'])
                ->where('postal', $address['postal'])
                ->where('street_number', $address['street_number'])
                ->where('street_number_ext', $address['street_number_ext'])
                ->whereNull('street_number_add')
                ->first();
        } elseif (array_keys($address) == ['postal', 'street_number', 'street_number_add']) {
            $address = Address::query()
                ->with(['publicSpace.place', 'residentialObject.buildings'])
                ->where('postal', $address['postal'])
                ->where('street_number', $address['street_number'])
                ->whereNull('street_number_ext')
                ->where('street_number_add', $address['street_number_add'])
                ->first();
        } elseif (array_keys($address) == ['postal', 'street_number', 'street_number_ext', 'street_number_add']) {
            $address = Address::query()
                ->with(['publicSpace.place', 'residentialObject.buildings'])
                ->where('postal', $address['postal'])
                ->where('street_number', $address['street_number'])
                ->where('street_number_ext', $address['street_number_ext'])
                ->where('street_number_add', $address['street_number_add'])
                ->first();
        } else {
            return response([
                'message' => 'Invalid address',
            ], 400);
        }

        if ($address) {
            return response([
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
            ]);
        }

        return response([
            'message' => 'Address not found.',
        ], 404);
    }
}
