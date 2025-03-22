<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GeocodeApiClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


#[Route('/api/test')]
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
    #[OA\Get(
        path: "/api/test/geocode/{postalCode}",
        summary: "Get geocode position by postal code",
        tags: ["Geocode"],
        parameters: [
            new OA\Parameter(
                name: "postalCode",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the geocode position",
                content: new OA\JsonContent(type: "object")
            )
        ]
    )]
    #[Route('/geocode/{postalCode}', name: 'app_geocode', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(string $postalCode): Response
    {
        // Get the response from the GeocodeApiClient service
        $response = $this->geocodeApiClient->getGeocodePosition($postalCode);

        // Return a JSON response
        return new JsonResponse($response, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }
}
