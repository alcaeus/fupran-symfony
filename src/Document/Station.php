<?php

namespace App\Document;

use GeoJson\Geometry\Point;
use Symfony\Component\Uid\UuidV4;

class Station
{
    public readonly UuidV4 $id;
    public string $name;
    public string $brand;
    public Address $address;
    public Point $location;

    public function __construct(string|UuidV4|null $id = null)
    {
        $this->id = $id instanceof UuidV4 ? $id : new UuidV4($id);
    }
}
