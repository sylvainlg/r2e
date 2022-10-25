<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApiUserController extends AbstractController
{

    #[Route("/api/user/me", name: "api_user_me")]
    public function me()
    {
        $user = $this->getUser();

        return $this->json([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
