<?php

namespace App\Controller;

use App\Codec\StationCodec;
use MongoDB\BSON\Binary;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use function base64_decode;
use function iterator_to_array;

class StationController extends AbstractController
{
    public function __construct(
        #[AutowireCollection(
            clientId: 'default',
            databaseName: '%databaseName%',
            collectionName: 'stations',
            # TODO: support using controller DI values in options
//            options: ['codec' => '@' . ImportedPriceCodec::class],
        )]
        private readonly Collection $collection,
        private readonly StationCodec $codec,
    ) {}

    #[Route('/stations', name: 'app_stations')]
    public function index(): JsonResponse
    {
        $stations = $this->collection->find([], ['codec' => $this->codec, 'limit' => 10]);

        return $this->json(iterator_to_array($stations));
    }

    #[Route('/stations/{id}', name: 'app_stations_show')]
    public function show(string $id): JsonResponse
    {
        $station = $this->collection->findOne(['_id' => new Binary(base64_decode($id), Binary::TYPE_UUID)], ['codec' => $this->codec]);

        return $this->json($station);
    }
}
