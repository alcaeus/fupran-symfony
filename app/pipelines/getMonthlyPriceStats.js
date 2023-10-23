db.priceReports.aggregate([
    // Select the big ones - these make up 86% of all petrol stations
    {
        $match: {
            'station.brand': { $in: ['ARAL', 'Shell', 'ESSO', 'TotalEnergies', 'AVIA', 'JET'] }
        }
    },
    // Group by year/month, fuelType, and brand
    {
        $group: {
            _id: {
                year: { $year: '$reportDate' },
                month: { $month: '$reportDate' },
                fuelType: '$fuelType',
                brand: '$station.brand',
            },
            lowest: { $min: '$price' },
            highest: { $max: '$price' },
            average: { $avg: '$price' },
            count: { $sum: 1 },
        },
    },
    {
        $group: {
            _id: {
                year: '$_id.year',
                month: '$_id.month',
                brand: '$_id.brand',
            },
            count: { $sum: '$count' },
            prices: {
                $push: {
                    k: '$_id.fuelType',
                    v: {
                        lowest: '$lowest',
                        highest: '$highest',
                        average: '$average',
                        span: { $subtract: ['$highest', '$lowest'] }
                    },
                }
            }
        },
    },
    {
        $addFields: {
            prices: { $arrayToObject: '$prices' }
        }
    },
    {
        $group: {
            _id: {
                year: '$_id.year',
                month: '$_id.month',
            },
            brands: {
                $push: {
                    brand: '$_id.brand',
                    count: '$count',
                    prices: '$prices',
                }
            }
        }
    },
    {
        $addFields: {
            brands: {
                $sortArray: {
                    input: '$brands',
                    sortBy: { 'count': -1 }
                }
            }
        }
    },
    {
        $sort: {
            '_id.year': 1,
            '_id.month': 1,
        }
    }
]);
