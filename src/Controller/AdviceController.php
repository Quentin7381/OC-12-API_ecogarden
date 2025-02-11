<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Entity\Advice;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class AdviceController extends AbstractController
{
    public function __construct(
        protected SerializerInterface $serializer
    ) {
    }

    #[Route('/api/v1/advices', name: 'app_advice', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response {
        // Get all advices from the database
        $advices = $entityManager->getRepository(Advice::class)->findAll();

        // Serialize the data with groups
        $data = $this->serializer->serialize($advices, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/v1/advices', name: 'app_advice_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): Response {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            throw new HttpException(400, "Couldn't parse JSON body");
        }

        $advice = new Advice();
        $advice->setMonth($data['month']);
        $advice->setTitle($data['title']);
        $advice->setContent($data['content']);
        
        if(isset($data['author'])) {
            if(
                $this->isGranted('ROLE_ADMIN')
                || $this->getUser()->getId() === $data['author']
            ) {
                $author = $entityManager->getRepository(User::class)->find($data['author']);
                $advice->setAuthor($author);
            } else {
                throw new HttpException(403, "You are not allowed to set another author than yourself");
            }
        } else {
            $author = $this->getUser();
            $advice->setAuthor($author);
        }

        // Save the advice to the database
        $entityManager->persist($advice);
        $entityManager->flush();

        // Serialize the data with groups
        $data = $this->serializer->serialize($advice, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new Response($data, 201, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/v1/advices/{id}', name: 'app_advice_get', methods: ['GET'])]
    public function get(EntityManagerInterface $entityManager, int $id): Response {
        // Get the advice from the database
        $advice = $entityManager->getRepository(Advice::class)->find($id);

        // Serialize the data with groups
        $data = $this->serializer->serialize($advice, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/v1/advices/{id}', name: 'app_advice_update', methods: ['PUT', 'PATCH'])]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): Response {
        // Get the advice from the database
        $advice = $entityManager->getRepository(Advice::class)->find($id);

        // If the advice doesn't exist, return a 404 Not Found response
        if (!$advice) {
            throw new HttpException(404, "Advice not found");
        }

        // Check access rights
        $this->denyAccessUnlessGranted('edit', $advice);

        // Decode the JSON data
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            throw new HttpException(400, "Couldn't parse JSON body");
        }

        // Update the advice's data
        if (isset($data['month'])) {
            $advice->setMonth($data['month']);
        }
        if (isset($data['title'])) {
            $advice->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $advice->setContent($data['content']);
        }

        // Save the advice to the database
        $entityManager->persist($advice);
        $entityManager->flush();

        // Serialize the data with groups
        $data = $this->serializer->serialize($advice, 'json', ['groups' => 'advice:read']);

        // Return a JSON response
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/v1/users/{id}/advices', name: 'app_user_advices', methods: ['GET'])]
    public function userAdvices(EntityManagerInterface $entityManager, int $id): Response {
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
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }
}
