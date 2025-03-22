<?php

namespace App\Controller;

use App\Service\ApiClient;
use App\Service\GeocodeApiClient;
use App\Service\OpenMeteoApiClient;
use App\Service\Validator\Validator;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use OpenApi\Attributes as OA;

#[Route('/api')]
final class MeteoController extends AbstractController
{
    public function __construct(
        private ApiClient $apiClient,
        private GeocodeApiClient $geocodeApiClient,
        private OpenMeteoApiClient $openMeteoApiClient,
        protected Validator $validator
    ) {
    }

    /**
     * Returns the meteo data for a given postal code
     * 
     * @return JsonResponse The meteo data
     * 
     */
    #[OA\Get(
        path: "/api/meteo/{postal_code}",
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
                response: Response::HTTP_OK,
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
        return new JsonResponse($response, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Returns the meteo data for the user's postal code
     * 
     * @return JsonResponse The meteo data
     * 
     */
    #[OA\Get(
        path: "/api/meteo",
        summary: "Get default meteo data for the authenticated user",
        tags: ["Meteo"],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the meteo data",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthorized"
            )
        ]
    )]
    #[Route('/meteo', name: 'app_meteo_default', methods: ['GET'])]
    public function default(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $postal_code = $user->getPostalCode();

        $response = $this->openMeteoApiClient->getMeteoData($postal_code);

        return new JsonResponse($response, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
