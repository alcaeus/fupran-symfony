<?php

namespace App\Import;

use App\Codec\BinaryUuidCodec;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use MongoDB\Driver\BulkWrite;

use function strtotime;

final class PricesImporter extends Importer
{
    public function __construct(
        #[AutowireCollection]
        Collection $priceReports,
        private readonly BinaryUuidCodec $uuidCodec,
    )
    {
        parent::__construct($priceReports);
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
            'station' => $this->uuidCodec->encode($rawData['station_uuid']),
            'fuelType' => $fuelType,
            'price' => (float) $rawData[$fuelType],
        ];
    }
}
