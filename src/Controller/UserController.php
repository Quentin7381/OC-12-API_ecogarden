<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


final class UserController extends AbstractController
{
    #[Route('/user', name: 'api_user', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get all users from the database
        $users = $entityManager->getRepository(User::class)->findAll();

        // Return a JSON response
        return $this->json($users, 200);
    }

    #[Route('/user', name: 'api_user_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Create a new user
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($data['password']);
        $user->setPostalCode($data['postal_code']);
        $user->setRoles(['ROLE_USER']);

        // Save the user to the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Return a 201 Created response
        return $this->json($user, 201);
    }
    
}
