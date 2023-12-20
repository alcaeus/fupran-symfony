<?php

namespace App\Import;

use App\Codec\BinaryUuidCodec;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use MongoDB\Driver\BulkWrite;

final class StationsImporter extends Importer
{
    public function __construct(
        #[AutowireCollection]
        Collection $stations,
        private readonly BinaryUuidCodec $uuidCodec,
    ) {
        parent::__construct($stations);
    }

    protected function storeDocument(BulkWrite $bulk, array $data): void
    {
        $bulk->update(
            $this->buildQuery($data),
            $this->buildDocument($data),
            ['upsert' => true],
        );
    }

    private function buildDocument(array $rawData): array
    {
        return [
            'name' => $rawData['name'],
            'brand' => $rawData['brand'],
            'address' => [
                'street' => $rawData['street'],
                'houseNumber' => $rawData['house_number'],
                'postCode' => $rawData['post_code'],
                'city' => $rawData['city'],
            ],
            'location' => [
                'type' => 'Point',
                'coordinates' => [
                    (float) $rawData['longitude'],
                    (float) $rawData['latitude'],
                ],
            ],
        ];
    }

    private function buildQuery(array $rawData): array
    {
        return [
            '_id' => $this->uuidCodec->encode($rawData['uuid']),
        ];
    }
}
