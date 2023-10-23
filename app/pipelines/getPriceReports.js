db.prices.aggregate([
    // Filter by date if we wanted to: use $match
    // Add a reports field that contains reports for all prices that changed
    {
        $addFields: {
            reports: {
                $filter: {
                    input: [
                        {
                            fuelType: 'diesel',
                            changed: '$dieselchange',
                            price: '$diesel'
                        },
                        {
                            fuelType: 'e5',
                            changed: '$e5change',
                            price: '$e5'
                        },
                        {
                            fuelType: 'e10',
                            changed: '$e10change',
                            price: '$e10'
                        },
                    ],
                    cond: { $eq: ['$$report.changed', true] },
                    as: 'report'
                }
            },
        }
    },
    // Unwind the reports - this creates a single document for each fuel type
    {
        $unwind: '$reports'
    },
    // Pull fuelType and price fields to the root
    {
        $addFields: {
            fuelType: '$reports.fuelType',
            price: '$reports.price',
        }
    },
    // Remove fields we don't need anymore. ID is removed as we'd have duplicates due to the previous unwind
    {
        $unset: ["_id", "reports", "diesel", "dieselchange", "e5", "e5change", "e10", "e10change"]
    },
    // Lookup certain station data
    {
        $lookup: {
            from: 'stations',
            localField: 'station',
            foreignField: '_id',
            as: 'station',
            pipeline: [
                {
                    $unset: ["address.street", "address.houseNumber", "address.city", "location"]
                }
            ]
        }
    },
    // Only keep first station data
    {
        $addFields: {
            station: { $first: '$station' }
        }
    }
]);
