<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Service\Validator\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;


use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/v1")
 */
#[Route('/api/v1')]
final class UserController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordEncoder,
        protected SerializerInterface $serializer,
        protected Validator $validator
    ) {
    }

    /**
     * Create a new user
     * 
     * @return Response The user data
     * 
     */
    #[OA\Get(
        path: "/api/v1/users",
        summary: "Get all users",
        tags: ["User"],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the list of users",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: new Model(type: User::class, groups: ["user:read"])))
            )
        ]
    )]
    #[Route('/users', name: 'api_user', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get all users from the database
        $users = $entityManager->getRepository(User::class)->findAll();

        // Serialize the data with groups
        $data = $this->serializer->serialize($users, 'json', ['groups' => 'user:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

    /**
     * Get a user by ID
     * 
     * @return Response The user data
     * 
     */
    #[OA\Get(
        path: "/api/v1/users/{id}",
        summary: "Get a user by ID",
        tags: ["User"],
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
                description: "Returns the user",
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ["user:read"]))
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "User not found"
            )
        ]
    )]
    #[Route('/users/{id}', name: 'api_user_get', methods: ['GET'])]
    public function get(EntityManagerInterface $entityManager, int $id): Response
    {
        // Get the user from the database
        $user = $entityManager->getRepository(User::class)->find($id);

        // If the user doesn't exist, return a 404 Not Found response
        if (!$user) {
            throw new HttpException(404, "User not found");
        }

        // Serialize the data with groups
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

    /**
     * Update a user by ID
     * 
     * @return JsonResponse The user data
     * 
     */
    #[OA\Put(
        path: "/api/v1/users/{id}",
        summary: "Update a user by ID",
        tags: ["User"],
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
                    new OA\Property(property: "username", type: "string"),
                    new OA\Property(property: "postal_code", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Returns the updated user",
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ["user:read"]))
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "User not found"
            )
        ]
    )]
    #[Route('/users/{id}', name: 'api_user_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        // Get the user from the database
        $user = $entityManager->getRepository(User::class)->find($id);

        // If the user doesn't exist, return a 404 Not Found response
        if (!$user) {
            throw new HttpException(404, "User not found");
        }

        // Check access rights
        $this->denyAccessUnlessGranted('edit', $user);

        // Decode the JSON data
        $data = $this->validator->fill($request, [
            'body' => [
                'username' => '?user::username',
                'postal_code' => '?user::postal_code',
                'roles' => '?user::roles',
                'password' => '?user::password'
            ]
        ], $user);

        // Save the user to the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Hash the password with symfony's password encoder
        if (!empty($data['password'])) {
            $password = $data['password'];
            $hash = $this->passwordEncoder->hashPassword($user, $password);
            $user->setPassword($hash);
        }

        // Serialize the data with groups
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

        // Return a JSON response
        return new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

    #[OA\Post(
        path: "/api/v1/users",
        summary: "Register a new user",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "username", type: "string"),
                    new OA\Property(property: "postal_code", type: "string"),
                    new OA\Property(property: "password", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "User created",
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ["user:read"]))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input"
            )
        ]
    )]
    #[Route('/users', name: 'api_user_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = new User();
        $data = $this->validator->fill($request, [
            'body' => [
                'username' => 'user::username',
                'postal_code' => 'user::postal_code',
                'password' => 'user::password',
                'roles' => 'user::roles'
            ]
        ], $user);

        // Hash the password with symfony's password encoder
        $data = $data['body'];
        $password = $data['password'];
        $hash = $this->passwordEncoder->hashPassword($user, $password);
        $user->setPassword($hash);

        // Save the user to the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Serialize the data with groups
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

        // Return a 201 Created response without re-serializing the data
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[OA\Delete(
        path: "/api/v1/users/{id}",
        summary: "Delete a user by ID",
        tags: ["User"],
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
                description: "User deleted"
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "User not found"
            )
        ]
    )]
    #[Route('/users/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw new HttpException(404, "User not found");
        }

        // Check access rights
        $this->denyAccessUnlessGranted('delete', $user);

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
