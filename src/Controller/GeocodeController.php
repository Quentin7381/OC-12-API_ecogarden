<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GeocodeApiClient;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/api/v1/test')]
final class GeocodeController extends AbstractController
{
    public function __construct(
        private GeocodeApiClient $geocodeApiClient
    ) {
    }

    /**
     * Returns the geocode position for a given postal code
     * 
     * @return JsonResponse The geocode position
     * 
     */
    #[Route('/geocode/{postalCode}', name: 'app_geocode', methods: ['GET'])]
    public function index(string $postalCode): Response
    {
        // Get the response from the GeocodeApiClient service
        $response = $this->geocodeApiClient->getGeocodePosition($postalCode);

        // Return a JSON response
        return new JsonResponse($response, 200, ['Content-Type' => 'application/json']);
    }
}
