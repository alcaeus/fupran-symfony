<?php

namespace App\Pipeline;

final class DenormalizeStations implements Pipeline
{
    /** @return array<object> */
    public function getPipeline(): array
    {
        return [
            $this->matchOnlyMissingStationRecords(),
            $this->lookupStation(),
            $this->extractFirstStation(),
            $this->mergeIntoPriceReports(),
        ];
    }

    private function matchOnlyMissingStationRecords(): object
    {
        return (object) [
            '$match' => [
                'station._id' => ['$exists' => false],
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
            '$project' => [
                '_id' => true,
                'station' => ['$first' => '$station'],
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
