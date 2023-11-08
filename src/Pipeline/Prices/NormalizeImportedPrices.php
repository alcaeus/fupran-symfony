<?php

namespace App\Pipeline\Prices;

use App\Pipeline\Pipeline;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Stage;
use MongoDB\Builder\Type\StageInterface;
use stdClass;

use function MongoDB\object;

class NormalizeImportedPrices implements Pipeline
{
    public function getPipeline(): \MongoDB\Builder\Pipeline
    {
        return new \MongoDB\Builder\Pipeline(
            $this->addChangedPrices(),
            Stage::unwind(Expression::arrayFieldPath('changes')),
            $this->createFinalDocument(),
        );
    }

    private function createFuelDocument(string $fuel): stdClass
    {
        return object(
            fuel: $fuel,
            changed: Expression::toBool(Expression::fieldPath($fuel . 'change')),
            price: Expression::toDouble(Expression::fieldPath($fuel)),
        );
    }

    private function createDateFromString(): Expression\DateFromStringOperator
    {
        return Expression::dateFromString(dateString: Expression::stringFieldPath('date'));
    }

    private function addChangedPrices(): StageInterface
    {
        return Stage::addFields(
            changes: Expression::filter(
                input: [
                    $this->createFuelDocument('diesel'),
                    $this->createFuelDocument('e10'),
                    $this->createFuelDocument('e5'),
                ],
                cond: Expression::eq(
                    Expression::variable('this.changed'),
                    true,
                ),
            ),
        );
    }

    private function createFinalDocument(): StageInterface
    {
        // TODO: Should this work without object?
        return Stage::replaceWith(object(
            _id: Expression::concat(
                Expression::stringFieldPath('_id'),
                '-',
                Expression::stringFieldPath('changes.fuel'),
            ),

            // Date contains the original date, while day only contains the date portion
            date: $this->createDateFromString(),
            day: Expression::dateTrunc(
                date: $this->createDateFromString(),
                unit: 'day',
                timezone: '+01',
            ),

            // Prepare an embedded document with station data. We will add full station data later
            station: object(_id: Expression::fieldPath('station_uuid')),

            // Price data
            fuel: Expression::stringFieldPath('changes.fuel'),
            price: Expression::doubleFieldPath('changes.price'),
        ));
    }
}
