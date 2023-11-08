[
    { $match: {
        dailyAverage: { $exists: false }
    }},
    { $addFields: {
        dailyId: {
            day: "$day",
            fuel: "$fuel",
        },
    }},
    { $lookup: {
        from: "prices_daily_averages",
        localField: "dailyId",
        foreignField: "_id",
        as: "dailyAverage",
    }},
    { $addFields: {
        dailyAverage: { $first: "$dailyAverage" },
    }},
    { $project: {
        _id: true,
        "dailyAverage.lowestPrice": true,
        "dailyAverage.highestPrice": true,
        "dailyAverage.averagePrice": true,
        "dailyAverage.percentiles": true,
    }},
    { $merge: {
        into: "prices_daily"
    }},
]
