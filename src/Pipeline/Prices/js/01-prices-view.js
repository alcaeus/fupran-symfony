const pipeline = [
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
];
