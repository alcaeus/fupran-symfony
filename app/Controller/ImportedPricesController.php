<?php

namespace App\Controller;

use App\Document\ImportedPrice;
use MongoDB\BSON\ObjectId;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use function iterator_to_array;

class ImportedPricesController extends AbstractController
{
    public function __construct(
        #[AutowireCollection(
            clientId: 'default',
            databaseName: '%databaseName%',
            collectionName: 'priceReports',
            documentClass: ImportedPrice::class,
        )]
        private readonly Collection $collection,
    ) {}

    #[Route('/prices', name: 'app_imported_prices')]
    public function index(): JsonResponse
    {
        $prices = $this->collection->find([], ['limit' => 10]);

        return $this->json(iterator_to_array($prices));
    }

    #[Route('/prices/{id}', name: 'app_imported_price_show')]
    public function show(string $id): JsonResponse
    {
        $price = $this->collection->findOne(['_id' => new ObjectId($id)]);

        return $this->json($price);
    }
}
