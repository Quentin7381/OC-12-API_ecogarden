<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Security\TokenAuthenticator;
use App\Repository\UserRepository;


class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $user = $userRepository->findOneBy(['username' => $username]);

        if(!$user) {
            return $this->json(['message' => "Invalid username"], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }
}