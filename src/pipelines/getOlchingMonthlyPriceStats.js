db.priceReports.aggregate([
    // Filter by station post code and fuel type
    {
        $match: {
            'station.address.postCode': 82140,
        },
    },
    // Group by year/month and calculate basic statistics
    {
        $group: {
            _id: {
                station: '$station',
                fuelType: '$fuelType',
                year: { $year: '$reportDate' },
                month: { $month: '$reportDate' },
                day: { $dayOfMonth: '$reportDate' },
            },
            lowestPrice: { $min: '$price' },
            highestPrice: { $max: '$price' },
            prices: {
                $push: {
                    previous: '$previous',
                    reportDate: '$reportDate',
                    price: '$price',
                },
            }
        },
    },
    // Sort prices by their time
    {
        $addFields: {
            prices: {
                $sortArray: {
                    input: {
                        $filter: {
                            input: '$prices',
                            cond: { $ne: ['$$this.previous._id', null] },
                        },
                    },
                    sortBy: { 'reportDate': 1 },
                },
            },
        },
    },
    // Compute weighted average
    {
        $addFields: {
            // weightedPrices will be an array of documents containing the price and duration of each segment
            weightedPrices: {
                $map: {
                    input: '$prices',
                    as: 'priceReport',
                    in: {
                        duration: {
                            // No duration shall be longer than the time since midnight
                            // This is relevant for the first price report for each day
                            $min: [
                                {
                                    $dateDiff: {
                                        startDate: '$$priceReport.previous.reportDate',
                                        endDate: '$$priceReport.reportDate',
                                        unit: 'second',
                                    },
                                },
                                {
                                    $add: [
                                        { $multiply: [ { $hour: '$$priceReport.reportDate' }, 3600 ] },
                                        { $multiply: [ { $minute: '$$priceReport.reportDate' }, 60 ] },
                                        { $second: '$$priceReport.reportDate' },
                                    ]
                                },
                            ],
                        },
                        price: '$$priceReport.previous.price',
                    },
                },
            },
            // lastPrice is the last price for the given day, which is appended later
            lastPrice: { $last: '$prices' },
        }
    },
    {
        $addFields: {
            // Add the last price of the day to the weightedPrices array
            weightedPrices: {
                $concatArrays: [
                    '$weightedPrices',
                    [
                        {
                            duration: {
                                // Duration is the number of seconds remaining for that day
                                $subtract: [
                                    86400,
                                    {
                                        $add: [
                                            { $multiply: [ { $hour: '$lastPrice.reportDate' }, 3600 ] },
                                            { $multiply: [ { $minute: '$lastPrice.reportDate' }, 60 ] },
                                            { $second: '$lastPrice.reportDate' },
                                        ],
                                    },
                                ],
                            },
                            price: '$lastPrice.price',
                        },
                    ],
                ],
            },
        }
    },
    {
        $addFields: {
            // Each weighted price is the price times the number of seconds it was valid
            weightedPrices: {
                $map: {
                    input: '$weightedPrices',
                    in: {
                        $multiply: ['$$this.duration', '$$this.price'],
                    },
                },
            },
            averagePrice: {
                // The true weighted average is the weighted price divided by the number of seconds each day
                $divide: [
                    {
                        $reduce: {
                            input: '$weightedPrices',
                            initialValue: 0,
                            in: {
                                $add: [
                                    '$$value',
                                    { $multiply: ['$$this.duration', '$$this.price'] },
                                ],
                            },
                        },
                    },
                    86400
                ]
            }
        }
    }
]);
