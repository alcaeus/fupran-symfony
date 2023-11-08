[
    { $match: {
        "lowestPrice.price": { $gt: 0 },
    }},
    { $group: {
        _id: {
            day: "$day",
            fuel: "$fuel",
        },
        lowestPrice: { $min: "$lowestPrice.price" },
        highestPrice: { $max: "$highestPrice.price" },
        averagePrice: { $avg: "$weightedAverage" },
        percentiles: { $percentile: {
            input: "$weightedAverage",
            p: [0.5, 0.9, 0.95, 0.99],
            method: "approximate",
        }},
    }},
    { $addFields: {
        percentiles: { $arrayToObject: {
            $zip: {
                inputs: [
                    ["p50", "p90", "p95", "p99"],
                    "$percentiles",
                ],
            },
        }},
    }},
]
