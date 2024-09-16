<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityProfilController extends AbstractController
{
    #[Route('/secProfil', name: 'app_security_profil')]
    public function index(): Response
    {
        return $this->render('security_profil/index.html.twig', [
            'controller_name' => 'SecurityProfilController',
        ]);
    }
}
