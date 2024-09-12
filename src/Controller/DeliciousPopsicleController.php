<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeliciousPopsicleController extends AbstractController
{
    #[Route('/', name: 'app_delicious_popsicle')]
    public function index(): Response
    {
        return $this->render('delicious_popsicle/index.html.twig', [
            'controller_name' => 'DeliciousPopsicleController',
        ]);
    }
}
