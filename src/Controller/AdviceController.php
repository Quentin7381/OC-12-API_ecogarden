<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Entity\Advice;
use App\Entity\User;
use App\Service\Validator\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use ApiPlatform\OpenApi\Model\SecurityScheme;


#[Route('/api')]
final class AdviceController extends AbstractController
{
    public function __construct(
        protected SerializerInterface $serializer,
        protected Validator $validator
    ) {
    }

    #[OA\Get(
        path: "/api/advices",
        summary: "Get all advices",
        tags: ["Advice"],
        parameters: [],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the list of advices",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: new Model(type: Advice::class, groups: ["advice:read"])))
            )
        ],
    )]
    #[Route('/advices', name: 'app_advice', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $advices = $entityManager->getRepository(Advice::class)->findAll();

        // Serialize the data with groups
        $data = $this->serializer->serialize($advices, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

    #[OA\Get(
        path: "/api/advices/{month}",
        summary: "Get all advices by month",
        tags: ["Advice"],
        parameters: [
            new OA\Parameter(
                name: "month",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the list of advices",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: new Model(type: Advice::class, groups: ["advice:read"])))
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "No advices found for the given month"
            )
        ]
    )]

    #[Route('/advices/{month}', name: 'app_advice_month', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getByMonth(EntityManagerInterface $entityManager, string $month): Response
    {
        $advices = $entityManager->getRepository(Advice::class)->findByMonth($month);

        // Serialize the data with groups
        $data = $this->serializer->serialize($advices, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

    /**
     * Create a new advice
     * 
     * @return JsonResponse The advice data
     * 
     */
    #[OA\Post(
        path: "/api/advices",
        summary: "Create a new advice",
        tags: ["Advice"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "month", type: "string"),
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "content", type: "string"),
                    new OA\Property(property: "author", type: "integer", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Advice created",
                content: new OA\JsonContent(ref: new Model(type: Advice::class, groups: ["advice:read"]))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input"
            )
        ]
    )]
    #[Route('/advices', name: 'app_advice_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $advice = new Advice();
        $this->validator->fill($request, [
            'body' => [
                'month' => 'advice::month',
                'title' => 'advice::title',
                'content' => 'advice::content',
                'author' => 'advice::author'
            ],
        ], $advice);

        // Save the advice to the database
        $entityManager->persist($advice);
        $entityManager->flush();

        // Serialize the data with groups
        $data = $this->serializer->serialize($advice, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_CREATED, ['Content-Type' => 'application/json'], true);
    }

    /**
     * Update an advice by ID
     * 
     * @return JsonResponse The advice data
     * 
     */
    #[OA\Put(
        path: "/api/advices/{id}",
        summary: "Update an advice by ID",
        tags: ["Advice"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "month", type: "string"),
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "content", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the updated advice",
                content: new OA\JsonContent(ref: new Model(type: Advice::class, groups: ["advice:read"]))
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Advice not found"
            )
        ]
    )]
    #[Route('/advices/{id}', name: 'app_advice_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        // Get the advice from the database
        $advice = $entityManager->getRepository(Advice::class)->find($id);

        // If the advice doesn't exist, return a 404 Not Found response
        if (!$advice) {
            throw new HttpException(404, "Advice not found");
        }

        // Check access rights
        $this->denyAccessUnlessGranted('edit', $advice);

        // Fill the advice with the new data
        $this->validator->fill($request, [
            'body' => [
                'month' => '?advice::month',
                'title' => '?advice::title',
                'content' => '?advice::content',
                'author' => '?advice::author'
            ]
        ], $advice);

        // Save the advice to the database
        $entityManager->persist($advice);
        $entityManager->flush();

        // Serialize the data with groups
        $data = $this->serializer->serialize($advice, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

    /**
     * Delete an advice by ID
     * 
     * @return JsonResponse The response status
     * 
     */
    #[OA\Delete(
        path: "/api/advices/{id}",
        summary: "Delete an advice by ID",
        tags: ["Advice"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Advice deleted"
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Advice not found"
            )
        ]
    )]
    #[Route('/advices/{id}', name: 'app_advice_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        // Get the advice from the database
        $advice = $entityManager->getRepository(Advice::class)->find($id);

        // If the advice doesn't exist, return a 404 Not Found response
        if (!$advice) {
            throw new HttpException(404, "Advice not found");
        }

        // Check access rights
        $this->denyAccessUnlessGranted('delete', $advice);

        // Remove the advice from the database
        $entityManager->remove($advice);
        $entityManager->flush();

        // Return a 204 No Content response
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get all advices by user ID
     * 
     * @return JsonResponse The advice data
     * 
     */
    #[OA\Get(
        path: "/api/users/{id}/advices",
        summary: "Get all advices by user ID",
        tags: ["Advice"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the list of advices",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: new Model(type: Advice::class, groups: ["advice:read"])))
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "User not found"
            )
        ]
    )]
    #[Route('/users/{id}/advices', name: 'app_user_advices', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userAdvices(EntityManagerInterface $entityManager, int $id): Response
    {
        // Get the user from the database
        $user = $entityManager->getRepository(User::class)->find($id);

        // If the user doesn't exist, return a 404 Not Found response
        if (!$user) {
            throw new HttpException(404, "User not found");
        }

        // Get all advices from the database
        $advices = $entityManager->getRepository(Advice::class)->findBy(['author' => $user]);

        // Serialize the data with groups
        $data = $this->serializer->serialize($advices, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }
}
