<?php

namespace App\Aggregation;

use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Stage;
use MongoDB\Builder\Type\StageInterface;

final class DenormalizeStations implements Aggregation
{
    public function getPipeline(): Pipeline
    {
        return new Pipeline(
            $this->matchOnlyMissingStationRecords(),
            $this->lookupSingleStation(),
            $this->mergeIntoPriceReports(),
        );
    }

    private function matchOnlyMissingStationRecords(): StageInterface
    {
        return Stage::match(...['station' => ['$exists' => true]]);
    }

    private function lookupSingleStation(): Pipeline
    {
        return new Pipeline(
            Stage::lookup(
                as: 'station',
                from: 'stations',
                localField: 'station',
                foreignField: '_id',
                pipeline: new Pipeline(
                    $this->removeUnnecessaryStationFields(),
                ),
            ),
            Stage::project(
                _id: true,
                station: Expression::arrayElemAt(
                    Expression::arrayFieldPath('station'),
                    0,
                ),
            ),
        );
    }

    private function removeUnnecessaryStationFields(): StageInterface
    {
        return Stage::unset(
            'address.street',
            'address.houseNumber',
            'address.city',
            'location',
        );
    }

    private function mergeIntoPriceReports(): StageInterface
    {
        return Stage::merge(
            into: 'priceReports',
            on: '_id',
            whenMatched: 'merge',
            whenNotMatched: 'discard',
        );
    }
}
