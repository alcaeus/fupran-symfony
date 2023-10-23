db.prices.aggregate([
    {
        $group: {
            _id: null,
            dieselChanges: { $sum: { $toInt: '$dieselchange' }},
            e5Changes: { $sum: { $toInt: '$e5change' }},
            e10Changes: { $sum: { $toInt: '$e10change' }},
        }
    },
    {
        $addFields: {
            totalChanges: { $sum: ['$dieselChanges', '$e5Changes', '$e10Changes'] }
        }
    },
]);
