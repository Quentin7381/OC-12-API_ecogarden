<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1')]
class AuthController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordEncoder,
        protected SerializerInterface $serializer
    ) {
    }

    #[Route('/register', name: 'api_user_create', methods: ['POST'])]
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
}