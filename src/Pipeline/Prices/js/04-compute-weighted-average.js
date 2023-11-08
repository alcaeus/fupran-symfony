[
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
    { $project: {
        _id: true,
        weightedAverage: true,
    }},
    { $merge: { into: "prices_daily" } }
]
