<?php

namespace App\Controller;

use App\Codec\StationCodec;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StationsController extends AbstractController
{
    public function __construct(
        #[AutowireCollection(options: ['codec' => StationCodec::class])]
        private Collection $stations,
    ) {}

    #[Route('/stations', name: 'app_stations')]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->stations
                ->find([], ['limit' => 10])
                ->toArray(),
        );
    }
}
