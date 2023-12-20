<?php

namespace App\Controller;

use App\Codec\BinaryUuidCodec;
use App\Codec\StationCodec;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class StationsController extends AbstractController
{
    public function __construct(
        #[AutowireCollection(codec: StationCodec::class)]
        private Collection $stations,
        private BinaryUuidCodec $uuidCodec,
    ) {}

    #[Route('/stations', name: 'app_stations')]
    public function index(): Response
    {
        return $this->render(
            'stations/index.html.twig',
            [
                'stations' => $this->stations->find([], ['batchSize' => 1, 'limit' => 12])->toArray(),
            ],
        );
    }

    #[Route('/stations/{uuid}', name: 'app_stations_show')]
    public function show(string $uuid): Response
    {
        if (! $this->uuidCodec->canEncode($uuid)) {
            throw new NotFoundHttpException();
        }

        $station = $this->stations->findOne(['_id' => $this->uuidCodec->encode($uuid)]);
        if (! $station) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            'stations/show.html.twig',
            [
                'station' => $station,
            ],
        );
    }
}
