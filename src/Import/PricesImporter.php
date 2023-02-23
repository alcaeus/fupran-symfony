<?php

namespace App\Import;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use function strtotime;

final class PricesImporter extends Importer
{
    protected function getNamespace(): string
    {
        return $this->databaseName . '.priceReports';
    }

    protected function storeDocument(BulkWrite $bulk, array $data): void
    {
        foreach ($this->buildDocuments($data) as $priceReport) {
            $bulk->insert($priceReport);
        }
    }

    private function buildDocuments(array $rawData): array
    {
        return array_filter([
            $this->buildDocument($rawData, 'diesel'),
            $this->buildDocument($rawData, 'e5'),
            $this->buildDocument($rawData, 'e10'),
        ]);
    }

    private function buildDocument(array $rawData, string $fuelType): ?array
    {
        if ($rawData[$fuelType . 'change'] !== '1') {
            return null;
        }

        return [
            'reportDate' => new UTCDateTime(strtotime($rawData['date']) * 1000),
            'station' => $this->createBinaryUuid($rawData['station_uuid']),
            'fuelType' => $fuelType,
            'price' => (float) $rawData[$fuelType],
        ];
    }
}
