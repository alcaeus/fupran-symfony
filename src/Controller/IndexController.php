<?php

namespace App\Controller;

use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(
        #[AutowireCollection()]
        Collection $stations,
        #[AutowireCollection()]
        Collection $priceReports,
    ): Response
    {
        return $this->render(
            'index/index.html.twig',
            [
                'stationCount' => $stations->estimatedDocumentCount(),
                'priceReportsCount' => $priceReports->estimatedDocumentCount(),
            ],
        );
    }
}
