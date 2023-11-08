[
    { $set: {
        date: { $dateFromString: { dateString: "$date" } },
        station: { _id: "$station_uuid" },
        changes: [
            {
                fuel: "diesel",
                changed: { $toBool: { $toInt: "$dieselchange" } },
                price: { $toDouble: "$diesel" },
            },
            {
                fuel: "e10",
                changed: { $toBool: { $toInt: "$e10change" } },
                price: { $toDouble: "$e10" },
            },
            {
                fuel: "e5",
                changed: { $toBool: { $toInt: "$e5change" } },
                price: { $toDouble: "$e5" },
            },
        ],
    }},
    { $set: {
        day: { $dateTrunc: {
            date: "$date",
            unit: "day",
            timezone: "+01",
        }},
        changes: { $filter: {
            input: "$changes",
            cond: { $eq: ["$$this.changed", true] },
        }},
    }},
    { $unwind: "$changes" },
    { $replaceWith: {
        _id: { $concat: ["$_id", "-", "$changes.fuel"] },
        date: "$date",
        day: "$day",
        station: "$station",
        fuel: "$changes.fuel",
        price: "$changes.price",
    }},
    { $group: {
        _id: {
            station: "$station._id",
            day: "$day",
            fuel: "$fuel",
        },
        prices: { $push: {
            _id: "$_id",
            date: "$date",
            price: "$price",
        }},
    }},
    { $replaceWith: {
        day: "$_id.day",
        station: { _id: "$_id.station" },
        fuel: "$_id.fuel",
        prices: { $sortArray: { input: "$prices", sortBy: { date: 1 } } },
        pricesByPrice: { $sortArray: { input: "$prices", sortBy: { price: 1 } } },
    }},
    { $addFields: {
        closingPrice: { $getField: { input: { $last: "$prices" }, field: "price" } },
        lowestPrice: { $first: "$pricesByPrice" },
        highestPrice: { $last: "$pricesByPrice" },
        pricesByPrice: "$$REMOVE",
    }},
    { $lookup: {
        from: "stations",
        localField: "station._id",
        foreignField: "_id",
        as: "station",
        pipeline: [
            { $project: {
                _id: true,
                name: true,
                brand: true,
                location: true,
                address: { postCode: true },
            }},
        ],
    }},
    { $addFields: { station: { $first: "$station" } } },
    { $addFields: {
        prices: { $map: {
            input: { $zip: {
                inputs: [
                    "$prices",
                    { $concatArrays: [
                        [{ previousPrice: null }],
                        { $map: {
                            input: {
                                $slice: [
                                    "$prices",
                                    { $subtract: [ { $size: "$prices" }, 1] },
                                ],
                            },
                            in: { previousPrice: "$$this.price" },
                        }},
                    ]},
                ],
                useLongestLength: true,
            }},
            in: { $mergeObjects: "$$this" }},
        },
    }},
    { $setWindowFields: {
        partitionBy: {
            station_id: "$station._id",
            fuel: "$fuel",
        },
        sortBy: { day: 1 },
        output: {
            openingPrice: { $shift: {
                output: "$closingPrice",
                by: -1,
                default: null,
            }},
        },
    }},
    { $addFields: {
        prices: { $map: {
            input: { $zip: {
                inputs: [
                    "$prices",
                    [{ previousPrice: "$openingPrice" }],
                ],
                useLongestLength: true,
                defaults: [{}, {}],
            }},
            in: { $mergeObjects: "$$this" }},
        },
    }},
    { $addFields: {
        prices: { $map: {
            input: "$prices",
            in: { $mergeObjects: [
                "$$this",
                { change: { $subtract: [ "$$this.price", "$$this.previousPrice" ] } },
            ]},
        }},
    }},
    { $addFields: {
        prices: { $concatArrays: [
            "$prices",
            [{
                date: { $dateAdd: {
                    startDate: "$day",
                    unit: "day",
                    amount: 1,
                }},
                previousPrice: "$closingPrice",
            }],
        ]},
        openingPrice: { $ifNull: [
            "$openingPrice",
            { $getField: {
                field: "price",
                input: { $arrayElemAt: ["$prices", 0] },
            }},
        ]},
    }},
    { $addFields: {
        prices: { $map: {
            input: { $zip: {
                inputs: [
                    "$prices",
                    { $concatArrays: [
                        [{ previousTime: "$day" }],
                        { $map: {
                            input: { $slice: [
                                "$prices",
                                { $subtract: [ { $size: "$prices" }, 1 ] },
                            ]},
                            in: {
                                previousTime:
                                    "$$this.date",
                            },
                        }},
                    ]},
                ],
                useLongestLength: true,
            }},
            in: {
                $mergeObjects: "$$this",
            },
        }},
    }},
    { $addFields: {
        prices: { $map: {
            input: "$prices",
            in: {
                seconds: {
                    $dateDiff: {
                        startDate: "$$this.previousTime",
                        endDate: "$$this.date",
                        unit: "second",
                    },
                },
                price: { $ifNull: [ "$$this.previousPrice", "$openingPrice" ] },
            },
        }},
    }},
    { $addFields: {
        weightedAverage: { $round: [
            { $divide: [
                { $reduce: {
                    input: "$prices",
                    initialValue: 0,
                    in: { $add: [
                        "$$value",
                        { $multiply: [ "$$this.seconds", "$$this.price" ] },
                    ]},
                }},
                { $dateDiff: {
                    startDate: "$day",
                    endDate: { $dateAdd: {
                        startDate: "$day",
                        unit: "day",
                        amount: 1,
                    }},
                    unit: "second",
                }},
            ]},
            3,
        ]},
    }},
]
