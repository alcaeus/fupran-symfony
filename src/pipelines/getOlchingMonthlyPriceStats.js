db.priceReports.aggregate([
    // Filter by station post code and fuel type
    {
        $match: {
            'station.address.postCode': '82140',
        },
    },
    // Group by year/month and calculate stats
    {
        $group: {
            _id: {
                year: { $year: '$reportDate' },
                month: { $month: '$reportDate' },
            },
            lowestPrice: { $min: '$price' },
            highestPrice: { $max: '$price' },
            averagePrice: { $avg: '$price' },
        },
    },
]);
