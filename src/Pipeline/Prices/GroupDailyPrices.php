<?php

namespace App\Pipeline\Prices;

use App\Pipeline\Pipeline;
use MongoDB\Builder\Accumulator;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Stage;

use function MongoDB\object;

class GroupDailyPrices implements Pipeline
{
    public function getPipeline(): \MongoDB\Builder\Pipeline
    {
        return new \MongoDB\Builder\Pipeline(
            $this->groupPricesByDay(),
            $this->sortPrices(),
            $this->addMostExtremePrices(),
            $this->lookupStationData(),
            $this->addPreviousPriceToPriceList(),
        );
    }

    private function groupPricesByDay(): Stage\GroupStage
    {
        return Stage::group(
            _id: object(
                station: Expression::fieldPath('station._id'),
                day: Expression::dateFieldPath('day'),
                fuel: Expression::stringFieldPath('fuel'),
            ),
            prices: Accumulator::push(object(
                _id: Expression::fieldPath('_id'),
                date: Expression::dateFieldPath('date'),
                price: Expression::doublefieldPath('price'),
            )),
        );
    }

    private function sortPrices(): Stage\ReplaceWithStage
    {
        return Stage::replaceWith(object(
            day: Expression::dateFieldPath('_id.day'),
            station: object(_id: Expression::fieldPath('_id.station')),
            fuel: Expression::stringFieldPath('_id.fuel'),
            prices: Expression::sortArray(
                input: Expression::arrayFieldPath('prices'),
                sortBy: object(date: -1),
            ),
            pricesByPrice: Expression::sortArray(
                input: Expression::arrayFieldPath('prices'),
                sortBy: object(price: -1),
            ),
        ));
    }

    private function addMostExtremePrices(): Stage\AddFieldsStage
    {
        return Stage::addFields(
            closingPrice: Expression::getField(
                field: 'price',
                input: Accumulator::last(Expression::arrayFieldPath('pricesByPrice')),
            ),
            lowestPrice: Accumulator::first(Expression::arrayFieldPath('pricesByPrice')),
            highest: Accumulator::last(Expression::arrayFieldPath('pricesByPrice')),
            pricesByPrice: Expression::variable('REMOVE'),
        );
    }

    private function lookupStationData(): \MongoDB\Builder\Pipeline
    {
        return new \MongoDB\Builder\Pipeline(
                Stage::lookup(
                as: 'station',
                from: 'stations',
                localField: 'station._id',
                foreignField: '_id',
                pipeline: new \MongoDB\Builder\Pipeline(
                    Stage::project(
                        _id: true,
                        name: true,
                        brand: true,
                        location: true,
                        address: object(postCode: true),
                    ),
                ),
            ),
            Stage::set(
                station: Accumulator::first(Expression::arrayFieldPath('station')),
            ),
        );
    }

    private function addPreviousPriceToPriceList(): Stage\SetStage
    {
        return Stage::set(
            prices: Expression::map(
                input: Expression::zip(
                    inputs: [
                        // Sorted price list
                        Expression::arrayFieldPath('prices'),
                        // Price listed shifted back by one, effectively adding the previous price to each element
                        $this->shiftPriceListBack(),
                    ],
                    useLongestLength: true,
                    // TODO: This should be optional
                    defaults: [],
                ),
                in: Expression::mergeObjects(Expression::variable('this')),
            )
        );
    }

    private function shiftPriceListBack(): Expression\ConcatArraysOperator
    {
        return Expression::concatArrays(
            [object(previousPrice: null)],
            Expression::map(
                input: $this->removeLastPriceFromList(),
                in: object(previousPrice: Expression::variable('this.price')),
            ),
        );
    }

    private function removeLastPriceFromList(): Expression\SliceOperator
    {
        return Expression::slice(
            expression: Expression::arrayFieldPath('prices'),
            n: Expression::subtract(Expression::size(Expression::arrayFieldPath('prices')), 1),
        );
    }
}
