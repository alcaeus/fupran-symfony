<?php

namespace App\Import;

use MongoDB\Driver\BulkWrite;

final class StationsImporter extends Importer
{
    protected function getNamespace(): string
    {
        return $this->databaseName . '.stations';
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
                'postCode' => (int) $rawData['post_code'],
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
            '_id' => $this->createBinaryUuid($rawData['uuid']),
        ];
    }
}
