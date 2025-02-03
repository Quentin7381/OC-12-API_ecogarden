<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;

final class UserController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordEncoder,
        protected SerializerInterface $serializer
    ) {
    }

    #[Route('/api/user', name: 'api_user', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get all users from the database
        $users = $entityManager->getRepository(User::class)->findAll();

        // Serialize the data with groups
        $data = $this->serializer->serialize($users, 'json', ['groups' => 'user:read']);

        // Return a JSON response
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/user', name: 'api_user_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            throw new HttpException(400, "Couldn't parse JSON body");
        }

        // Decode the JSON data
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            throw new HttpException(400, "Couldn't parse JSON body");
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPostalCode($data['postal_code']);
        $user->setRoles(['ROLE_USER']);

        // Hash the password with symfony's password encoder
        $password = $data['password'];
        $hash = $this->passwordEncoder->hashPassword($user, $password);
        $user->setPassword($hash);
        
        // Save the user to the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Serialize the data with groups
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

        // Return a 201 Created response
        return new Response($data, 201, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/user/{id}', name: 'api_user_get', methods: ['GET'])]
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
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/user/{id}', name: 'api_user_update', methods: ['PUT', 'PATCH'])]
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
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            throw new HttpException(400, "Couldn't parse JSON body");
        }

        // Update the user's data
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['postal_code'])) {
            $user->setPostalCode($data['postal_code']);
        }

        // Save the user to the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Serialize the data with groups
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

        // Return a JSON response
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

}
