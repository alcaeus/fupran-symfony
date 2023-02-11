<?php

namespace App\Pipeline;

final class GetPriceReports implements Pipeline
{
    /** @return array<object> */
    public function getPipeline(): array
    {
        return [
            $this->removeUnchangedPrices(),
            $this->unwindChangedPrices(),
            $this->addPriceFields(),
            $this->removeUnnecessaryPriceFields(),
            $this->lookupStation(),
            $this->extractFirstStation(),
        ];
    }

    private function removeUnchangedPrices(): object
    {
        return (object) [
            '$addFields' => [
                'reports' => [
                    '$filter' => [
                        'input' => [
                            $this->createPriceReport('diesel'),
                            $this->createPriceReport('e5'),
                            $this->createPriceReport('e10'),
                        ],
                        'cond' => [
                            '$eq' => ['$$report.changed', true]
                        ],
                        'as' => 'report',
                    ],
                ],
            ],
        ];
    }

    private function unwindChangedPrices(): object
    {
        return (object) ['$unwind' => '$reports'];
    }

    private function addPriceFields(): object
    {
        return (object) [
            '$addFields' => [
                'fuelType' => '$reports.fuelType',
                'price' => '$reports.price',
            ],
        ];
    }

    private function removeUnnecessaryPriceFields(): object
    {
        return (object) [
            '$unset' => [
                '_id',
                'reports',
                'diesel',
                'dieselchange',
                'e5',
                'e5change',
                'e10',
                'e10change'
            ],
        ];
    }

    private function lookupStation(): object
    {
        return (object) [
            '$lookup' => [
                'from' => 'stations',
                'localField' => 'station',
                'foreignField' => '_id',
                'as' => 'station',
                'pipeline' => [$this->removeUnnecessaryStationFields()],
            ],
        ];
    }

    private function removeUnnecessaryStationFields(): object
    {
        return (object) [
            '$unset' => [
                'address.street',
                'address.houseNumber',
                'address.city',
                'location',
            ],
        ];
    }

    private function extractFirstStation(): object
    {
        return (object) [
            '$addFields' => [
                'station' => ['$first' => '$station'],
            ],
        ];
    }

    private function createPriceReport(string $fuelType): object
    {
        return (object) [
            'fuelType' => $fuelType,
            'changed' => sprintf('$%schange', $fuelType),
            'price' => sprintf('$%s', $fuelType),
        ];
    }
}
