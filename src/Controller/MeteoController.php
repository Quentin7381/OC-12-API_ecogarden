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
use Symfony\Component\Security\Http\Attribute\IsGranted;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[Route('/api/v1')]
final class MeteoController extends AbstractController
{
    public function __construct(
        private ApiClient $apiClient,
        private GeocodeApiClient $geocodeApiClient,
        private OpenMeteoApiClient $openMeteoApiClient
    ) {
    }

    /**
     * Returns the meteo data for a given postal code
     * 
     * @return JsonResponse The meteo data
     * 
     */
    #[OA\Get(
        path: "/api/v1/meteo/{postal_code}",
        summary: "Get meteo data by postal code",
        tags: ["Meteo"],
        parameters: [
            new OA\Parameter(
                name: "postal_code",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Returns the meteo data",
                content: new OA\JsonContent(type: "object")
            )
        ]
    )]
    #[Route('/meteo/{postal_code}', name: 'app_meteo', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(string $postal_code): Response
    {
        $response = $this->openMeteoApiClient->getMeteoData($postal_code);
        return new JsonResponse($response, 200, ['Content-Type' => 'application/json'], true);
    }

    /**
     * Returns the meteo data for the user's postal code
     * 
     * @return JsonResponse The meteo data
     * 
     */
    #[OA\Get(
        path: "/api/v1/meteo",
        summary: "Get default meteo data for the authenticated user",
        tags: ["Meteo"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Returns the meteo data",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized"
            )
        ]
    )]
    #[Route('/meteo', name: 'app_meteo_default', methods: ['GET'])]
    public function default(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new HttpException(401, 'Unauthorized');
        }

        $postal_code = $user->getPostalCode();

        $response = $this->openMeteoApiClient->getMeteoData($postal_code);

        return new JsonResponse($response, 200, ['Content-Type' => 'application/json'], true);
    }
}
