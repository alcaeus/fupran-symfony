[
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
    { $project: {
        openingPrice: true,
        prices: true,
    }},
    { $merge: { into: "prices_daily" } },
]
