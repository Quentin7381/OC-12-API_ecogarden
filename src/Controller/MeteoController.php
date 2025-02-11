<?php

namespace App\Controller;

use App\Service\ApiClient;
use App\Service\GeocodeApiClient;
use App\Service\OpenMeteoApiClient;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/v1')]
final class MeteoController extends AbstractController
{
    public function __construct(
        private ApiClient $apiClient,
        private GeocodeApiClient $geocodeApiClient,
        private OpenMeteoApiClient $openMeteoApiClient
    ) {
    }

    #[Route('/meteo/{postal_code}', name: 'app_meteo')]
    public function index(string $postal_code): Response
    {
        $response = $this->openMeteoApiClient->getMeteoData($postal_code);
        return new JsonResponse($response, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/meteo', name: 'app_meteo_default')]
    public function default(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new HttpException(401, 'Unauthorized');
        }

        $postal_code = $user->getPostalCode();

        $response = $this->openMeteoApiClient->getMeteoData($postal_code);

        return new JsonResponse($response, 200, ['Content-Type' => 'application/json']);
    }
}
