<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ApiUserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/user/register', name: 'add_user', methods: ['POST'])]
    public function get(Request $request,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $data = json_decode($request->getContent(), true);

        if(empty($data['username']) || empty($data['password'])){
            return $this->json(['message'=>'Tous les champs doivent être renseignés'],Response::HTTP_BAD_REQUEST);
        }
        $email = $data['username'];
        $password = $data['password'];

        $newUser = new User();
        $newUser->setEmail($email);
        $newUser->setPassword(
            $userPasswordHasher->hashPassword(
                $newUser,
                $password
            )
        );
        $newUser->setIsVerified(true);

        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        return $this->json($newUser,Response::HTTP_CREATED,[]);
    }
}
