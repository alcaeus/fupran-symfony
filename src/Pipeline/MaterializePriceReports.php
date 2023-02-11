<?php

namespace App\Pipeline;

final class MaterializePriceReports implements Pipeline
{
    public function __construct(
        private readonly GetPriceReports $getPriceReports,
    ) {}

    /** @return array<object> */
    public function getPipeline(): array
    {
        return [
            ...$this->getPriceReports->getPipeline(),
            $this->mergeIntoPriceReports(),
        ];
    }

    private function mergeIntoPriceReports(): object
    {
        return (object) [
            '$merge' => [
                'into' => 'priceReports',
                'on' => ['reportDate', 'fuelType', 'station._id'],
                'whenMatched' => 'keepExisting',
                'whenNotMatched' => 'insert',
            ],
        ];
    }
}
