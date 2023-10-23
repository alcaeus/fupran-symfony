<?php

namespace App\Pipeline;

final class AddPreviousPrice implements Pipeline
{
    /** @return array<object> */
    public function getPipeline(): array
    {
        return [
            $this->matchOnlyMissingPreviousPriceRecords(),
            $this->lookupPrice(),
            $this->extractFirstPriceReport(),
            $this->addChangeFields(),
            $this->removeUnchangedFields(),
            $this->mergeIntoPriceReports(),
        ];
    }

    private function matchOnlyMissingPreviousPriceRecords(): object
    {
        return (object) [
            '$match' => [
                'previous._id' => ['$exists' => false],
            ],
        ];
    }

    private function lookupPrice(): object
    {
        return (object) [
            '$lookup' => [
                'from' => 'priceReports',
                'localField' => 'station._id',
                'foreignField' => 'station._id',
                'as' => 'previous',
                'let' => ['reportDate' => '$reportDate', 'fuelType' => '$fuelType'],
                'pipeline' => [
                    $this->onlyPreviousRecords(),
                    $this->sortByReportDate(),
                    $this->limitToSingleResult(),
                    $this->removeUnnecessaryPriceFields(),
                ],
            ],
        ];
    }

    private function onlyPreviousRecords(): object
    {
        return (object) [
            '$match' => [
                '$and' => [
                    [
                        '$expr' => [
                            '$lt' => ['$reportDate', '$$reportDate'],
                        ]
                    ],
                    [
                        '$expr' => [
                            '$eq' => ['$fuelType', '$$fuelType'],
                        ]
                    ],
                ],
            ],
        ];
    }

    private function sortByReportDate(): object
    {
        return (object) ['$sort' => ['reportDate' => -1]];
    }

    private function limitToSingleResult(): object
    {
        return (object) ['$limit' => 1];
    }

    private function removeUnnecessaryPriceFields(): object
    {
        return (object) [
            '$project' => [
                '_id' => true,
                'reportDate' => true,
                'price' => true,
            ],
        ];
    }

    private function extractFirstPriceReport(): object
    {
        return (object) [
            '$addFields' => [
                'previous' => ['$first' => '$previous'],
            ],
        ];
    }

    private function addChangeFields(): object
    {
        return (object) [
            '$addFields' => [
                'change' => [
                    'seconds' => [
                        '$dateDiff' => [
                            'startDate' => '$previous.reportDate',
                            'endDate' => '$reportDate',
                            'unit' => 'second',
                        ],
                    ],
                    'price' => [
                        '$subtract' => ['$price', '$previous.price']
                    ],
                ],
            ],
        ];
    }

    private function removeUnchangedFields(): object
    {
        return (object) [
            '$project' => [
                '_id' => true,
                'previous' => true,
                'change' => true,
            ],
        ];
    }

    private function mergeIntoPriceReports(): object
    {
        return (object) [
            '$merge' => [
                'into' => 'priceReports',
                'on' => '_id',
                'whenMatched' => 'merge',
                'whenNotMatched' => 'discard',
            ],
        ];
    }
}
