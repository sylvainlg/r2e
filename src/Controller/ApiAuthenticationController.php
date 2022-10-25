<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApiAuthenticationController extends AbstractController
{

    #[Route("/api/authentication/check", name: "api_authentication_check")]
    public function authentication_check()
    {
        $user = $this->getUser();

        return $this->json($user);
    }
}
