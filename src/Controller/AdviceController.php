<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Advice;
use Doctrine\ORM\EntityManagerInterface;

final class AdviceController extends AbstractController
{
    #[Route('/advice', name: 'app_advice', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response {
        // Get all advices from the database
        $advices = $entityManager->getRepository(Advice::class)->findAll();

        // Return a JSON response
        return $this->json($advices, 200);
    }
}
