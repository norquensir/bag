<?php

namespace App\Jobs;

use App\Models\Address;
use App\Models\BoatSpot;
use App\Models\Building;
use App\Models\File;
use App\Models\Place;
use App\Models\PublicSpace;
use App\Models\ResidentialObject;
use App\Models\TrailerSpot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Saloon\XmlWrangler\XmlReader;

class RunProcessJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 0;

    public File $file;

    public bool $once;

    public bool $last;

    public array $types = [
        'WPL',
        'OPR',
        'NUM',
        'LIG',
        'STA',
        'PND',
        'VBO',
    ];

    public function __construct($file, $once, $last)
    {
        $this->file = $file;
        $this->once = $once;
        $this->last = $last;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            switch ($this->file->type) {
                case 'WPL':
                    $this->places($this->file);
                    break;

                case 'OPR':
                    $this->public_spaces($this->file);
                    break;

                case 'NUM':
                    $this->addresses($this->file);
                    break;

                case 'LIG':
                    $this->boat_spots($this->file);
                    break;

                case 'STA':
                    $this->trailer_spots($this->file);
                    break;

                case 'PND':
                    $this->buildings($this->file);
                    break;

                case 'VBO':
                    $this->residential_objects($this->file);
                    break;

                default:
                    throw new \Exception('BagType is not valid.');
            }
        });

        if (!$this->once) {
            $lastKey = array_search($this->file->type, $this->types) + 1;

            if ($this->last && array_key_exists($lastKey, $this->types)) {
                RunZipJob::dispatch($this->types[$lastKey]);
            }
        }
    }

    private function places(File $file): void
    {
        $reader = XmlReader::fromFile(Storage::path($file->path));

        $reader->value('Objecten:Woonplaats')
            ->collectLazy()
            ->each(function (array $currentObject) {
                $identifier = $currentObject['Objecten:identificatie'];
                $name = $currentObject['Objecten:naam'];

                $place = Place::findByIdentifier($identifier);

                if (!$place) {
                    $place = new Place;
                    $place->identifier = $identifier;
                }

                $place->name = $name;
                $place->save();
            });

        if ($file->delete()) {
            Storage::delete($file->path);
        }
    }

    private function public_spaces(File $file): void
    {
        $reader = XmlReader::fromFile(Storage::path($file->path));

        $reader->value('Objecten:OpenbareRuimte')
            ->collectLazy()
            ->each(function (array $currentObject) {
                $identifier = $currentObject['Objecten:identificatie'];
                $name = $currentObject['Objecten:naam'];
                $placeIdentifier = $currentObject['Objecten:ligtIn']['Objecten-ref:WoonplaatsRef'];

                $publicSpace = PublicSpace::findByIdentifier($identifier);

                if (!$publicSpace) {
                    $publicSpace = new PublicSpace;
                    $publicSpace->identifier = $identifier;
                }

                $publicSpace->name = $name;
                $publicSpace->place_id = Place::findByIdentifier($placeIdentifier)->id;
                $publicSpace->save();
            });

        if ($file->delete()) {
            Storage::delete($file->path);
        }
    }

    private function addresses(File $file): void
    {
        $reader = XmlReader::fromFile(Storage::path($file->path));

        $reader->value('Objecten:Nummeraanduiding')
            ->collectLazy()
            ->each(function (array $currentObject) {
                $identifier = $currentObject['Objecten:identificatie'];
                $postal = $currentObject['Objecten:postcode'] ?? null;
                $streetNumber = $currentObject['Objecten:huisnummer'] ?? null;
                $streetNumberExt = $currentObject['Objecten:huisletter'] ?? null;
                $streetNumberAdd = $currentObject['Objecten:huisnummertoevoeging'] ?? null;
                $placeIdentifier = $currentObject['Objecten:ligtIn']['Objecten-ref:WoonplaatsRef'] ?? null;
                $publicSpaceIdentifier = $currentObject['Objecten:ligtAan']['Objecten-ref:OpenbareRuimteRef'];

                $address = Address::findByIdentifier($identifier);

                if (!$address) {
                    $address = new Address;
                    $address->identifier = $identifier;
                }

                $address->postal = $postal;
                $address->street_number = $streetNumber;
                $address->street_number_ext = $streetNumberExt;
                $address->street_number_add = $streetNumberAdd;
                $address->place_id = Place::findByIdentifier($placeIdentifier)?->id;
                $address->public_space_id = PublicSpace::findByIdentifier($publicSpaceIdentifier)->id;
                $address->save();
            });

        if ($file->delete()) {
            Storage::delete($file->path);
        }
    }

    private function boat_spots(File $file): void
    {
        $reader = XmlReader::fromFile(Storage::path($file->path));

        $reader->value('Objecten:Ligplaats')
            ->collectLazy()
            ->each(function (array $currentObject) {
                $identifier = $currentObject['Objecten:identificatie'];
                $posList = $currentObject['Objecten:geometrie']['gml:Polygon']['gml:exterior']['gml:LinearRing']['gml:posList'];
                $addressIdentifier = $currentObject['Objecten:heeftAlsHoofdadres']['Objecten-ref:NummeraanduidingRef'];
                $addressIdentifiers = $currentObject['Objecten:heeftAlsNevenadres']['Objecten-ref:NummeraanduidingRef'] ?? [];

                $coordsArray = explode(' ', $posList);
                $coordinates = [];

                for ($i = 0; $i < count($coordsArray); $i += 2) {
                    $x = $coordsArray[$i];
                    $y = $coordsArray[$i + 1];

                    $coordinates[] = [$x, $y];
                }

                $boatSpot = BoatSpot::findByIdentifier($identifier);

                if (!$boatSpot) {
                    $boatSpot = new BoatSpot;
                    $boatSpot->identifier = $identifier;
                }

                $boatSpot->latitude = $coordinates[0][0];
                $boatSpot->longitude = $coordinates[0][1];
                $boatSpot->polygons = json_encode([$coordinates], JSON_NUMERIC_CHECK);
                $boatSpot->address_id = Address::findByIdentifier($addressIdentifier)->id;
                $boatSpot->save();

                $addressIds = collect($addressIdentifiers)->map(function ($addressIdentifier) use ($boatSpot) {
                    return Address::findByIdentifier($addressIdentifier)->id;
                });

                $boatSpot->addresses()->sync($addressIds);
            });

        if ($file->delete()) {
            Storage::delete($file->path);
        }
    }

    private function trailer_spots(File $file): void
    {
        $reader = XmlReader::fromFile(Storage::path($file->path));

        $reader->value('Objecten:Standplaats')
            ->collectLazy()
            ->each(function (array $currentObject) {
                $identifier = $currentObject['Objecten:identificatie'];
                $posList = $currentObject['Objecten:geometrie']['gml:Polygon']['gml:exterior']['gml:LinearRing']['gml:posList'];
                $addressIdentifier = $currentObject['Objecten:heeftAlsHoofdadres']['Objecten-ref:NummeraanduidingRef'];

                $coordsArray = explode(' ', $posList);
                $coordinates = [];

                for ($i = 0; $i < count($coordsArray); $i += 2) {
                    $x = $coordsArray[$i];
                    $y = $coordsArray[$i + 1];

                    $coordinates[] = [$x, $y];
                }

                $trailerSpot = TrailerSpot::findByIdentifier($identifier);

                if (!$trailerSpot) {
                    $trailerSpot = new TrailerSpot;
                    $trailerSpot->identifier = $identifier;
                }

                $trailerSpot->latitude = $coordinates[0][0];
                $trailerSpot->longitude = $coordinates[0][1];
                $trailerSpot->polygons = json_encode([$coordinates], JSON_NUMERIC_CHECK);
                $trailerSpot->address_id = Address::findByIdentifier($addressIdentifier)->id;
                $trailerSpot->save();
            });

        if ($file->delete()) {
            Storage::delete($file->path);
        }
    }

    private function buildings(File $file): void
    {
        $reader = XmlReader::fromFile(Storage::path($file->path));

        $reader->value('Objecten:Pand')
            ->collectLazy()
            ->each(function (array $currentObject) {
                $identifier = $currentObject['Objecten:identificatie'];
                $posList = $currentObject['Objecten:geometrie']['gml:Polygon']['gml:exterior']['gml:LinearRing']['gml:posList'];

                $coordsArray = explode(' ', $posList);
                $coordinates = [];

                for ($i = 0; $i < count($coordsArray); $i += 3) {
                    $x = $coordsArray[$i];
                    $y = $coordsArray[$i + 1];
                    $z = $coordsArray[$i + 2];

                    $coordinates[] = [$x, $y, $z];
                }

                $building = Building::findByIdentifier($identifier);

                if (!$building) {
                    $building = new Building;
                    $building->identifier = $identifier;
                }

                $building->polygons = json_encode([$coordinates], JSON_NUMERIC_CHECK);
                $building->save();
            });

        if ($file->delete()) {
            Storage::delete($file->path);
        }
    }

    private function residential_objects(File $file): void
    {
        $reader = XmlReader::fromFile(Storage::path($file->path));

        $reader->value('Objecten:Verblijfsobject')
            ->collectLazy()
            ->each(function (array $currentObject) {
                $identifier = $currentObject['Objecten:identificatie'];
                $positions = $currentObject['Objecten:geometrie']['Objecten:punt']['gml:Point']['gml:pos'] ?? null;
                $addressIdentifier = $currentObject['Objecten:heeftAlsHoofdadres']['Objecten-ref:NummeraanduidingRef'];
                $buildingIdentifiers = $currentObject['Objecten:maaktDeelUitVan']['Objecten-ref:PandRef'];
                $residentialObject = ResidentialObject::findByIdentifier($identifier);

                if (!$residentialObject) {
                    $residentialObject = new ResidentialObject;
                    $residentialObject->identifier = $identifier;
                }

                if ($positions) {
                    $coordinates = Str::of($positions)->explode(' ');
                    $residentialObject->latitude = $coordinates[0];
                    $residentialObject->longitude = $coordinates[1];
                }

                $residentialObject->address_id = Address::findByIdentifier($addressIdentifier)->id;
                $residentialObject->save();

                $buildingIds = collect($buildingIdentifiers)->map(function ($buildingIdentifier) use ($residentialObject) {
                    return Building::findByIdentifier($buildingIdentifier)->id;
                });

                $residentialObject->buildings()->sync($buildingIds);
            });

        if ($file->delete()) {
            Storage::delete($file->path);
        }
    }
}
