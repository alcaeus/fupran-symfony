<?php

namespace App\Import;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use function strtotime;

final class PricesImporter extends Importer
{
    protected function getNamespace(): string
    {
        return $this->databaseName . '.prices';
    }

    protected function storeDocument(BulkWrite $bulk, array $data): void
    {
        $bulk->insert($this->buildDocument($data));
    }

    private function buildDocument(array $rawData): array
    {
        return [
            'reportDate' => new UTCDateTime(strtotime($rawData['date']) * 1000),
            'station' => $this->createBinaryUuid($rawData['station_uuid']),
            'diesel' => (float) $rawData['diesel'],
            'dieselchange' => $rawData['dieselchange'] === '1',
            'e5' => (float) $rawData['e5'],
            'e5change' => $rawData['e5change'] === '1',
            'e10' => (float) $rawData['e10'],
            'e10change' => $rawData['e10change'] === '1',
        ];
    }
}
