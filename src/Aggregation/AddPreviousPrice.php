<?php

namespace App\Aggregation;

use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Query;
use MongoDB\Builder\Stage;
use MongoDB\Builder\Type\StageInterface;

use function MongoDB\object;

final class AddPreviousPrice implements Aggregation
{
    public function getPipeline(): Pipeline
    {
        return new Pipeline(
            $this->matchOnlyMissingPreviousPriceRecords(),
            $this->lookupSinglePrice(),
            $this->addChangeFields(),
            $this->removeUnchangedFields(),
            $this->mergeIntoPriceReports(),
        );
    }

    private function matchOnlyMissingPreviousPriceRecords(): StageInterface
    {
        return Stage::match(...['previous._id' => ['$exists' => false]]);
    }

    private function lookupSinglePrice(): Pipeline
    {
        return new Pipeline(
            Stage::lookup(
                as: 'previous',
                from: 'priceReports',
                localField: 'station._id',
                foreignField: 'station._id',
                let: object(
                    reportDate: Expression::dateFieldPath('reportDate'),
                    fuelType: Expression::stringFieldPath('fuelType'),
                ),
                pipeline: new Pipeline(
                    $this->onlyPreviousRecords(),
                    # TODO: Should be able to pass sort specification using variadic args
                    Stage::sort(object(reportDate: -1)),
                    Stage::limit(1),
                    $this->removeUnnecessaryPriceFields(),
                ),
            ),
            # Unwind the array - this works better than $unwind in case somebody removes the $limit stage in $lookup
            Stage::addFields(
                previous: Expression::arrayElemAt(
                    Expression::arrayFieldPath('previous'),
                    0,
                ),
            ),
        );
    }

    private function onlyPreviousRecords(): StageInterface
    {
        return Stage::match(
            Query::query(Query::expr(Expression::lt(
                Expression::dateFieldPath('reportDate'),
                Expression::variable('reportDate'),
            ))),
            Query::query(Query::expr(Expression::eq(
                Expression::stringFieldPath('fuelType'),
                Expression::variable('fuelType'),
            ))),
        );
    }

    private function removeUnnecessaryPriceFields(): StageInterface
    {
        return Stage::project(
            _id: true,
            reportDate: true,
            price: true,
        );
    }

    private function addChangeFields(): StageInterface
    {
        return Stage::addFields(
            change: object(
                seconds: Expression::dateDiff(
                    startDate: Expression::dateFieldPath('previous.reportDate'),
                    endDate: Expression::dateFieldPath('reportDate'),
                    unit: 'second',
                ),
                price: Expression::subtract(
                    Expression::doubleFieldPath('price'),
                    Expression::doubleFieldPath('previous.price'),
                ),
            )
        );
    }

    private function removeUnchangedFields(): StageInterface
    {
        return Stage::project(
            _id: true,
            previous: true,
            change: true,
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
