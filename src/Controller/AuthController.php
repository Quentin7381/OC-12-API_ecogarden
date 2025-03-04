<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Service\Validator\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[Route('/api/v1')]
class AuthController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordEncoder,
        protected SerializerInterface $serializer,
        protected Validator $validator
    ) {
    }

    #[OA\Post(
        path: "/api/v1/register",
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
                response: 201,
                description: "User created",
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ["user:read"]))
            ),
            new OA\Response(
                response: 400,
                description: "Invalid input"
            )
        ]
    )]
    #[Route('/register', name: 'api_user_create', methods: ['POST'])]
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
        return new JsonResponse($data, 201, [], true);
    }
}