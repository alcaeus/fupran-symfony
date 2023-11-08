const pipeline = [
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
];
