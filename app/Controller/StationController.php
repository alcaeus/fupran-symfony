<?php

namespace App\Controller;

use App\Document\Station;
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
            documentClass: Station::class,
        )]
        private readonly Collection $stations,
    ) {}

    #[Route('/stations', name: 'app_stations')]
    public function index(): JsonResponse
    {
        $stations = $this->stations->find([], ['limit' => 10]);

        return $this->json(iterator_to_array($stations));
    }

    #[Route('/stations/{id}', name: 'app_stations_show')]
    public function show(string $id): JsonResponse
    {
        $station = $this->stations->findOne(['_id' => new Binary(base64_decode($id), Binary::TYPE_UUID)]);

        return $this->json($station);
    }
}
