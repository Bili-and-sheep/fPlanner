<?php

namespace App\Controller;

use App\Entity\Stand;
use App\Form\StandType;
use App\Repository\StandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stand')]
final class StandController extends AbstractController
{
    #[Route(name: 'app_stand_index', methods: ['GET'])]
    public function index(StandRepository $standRepository): Response
    {
        return $this->render('stand/index.html.twig', [
            'stands' => $standRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_stand_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stand = new Stand();
        $form = $this->createForm(StandType::class, $stand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stand);
            $entityManager->flush();

            return $this->redirectToRoute('app_stand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stand/new.html.twig', [
            'stand' => $stand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stand_show', methods: ['GET'])]
    public function show(Stand $stand): Response
    {
        return $this->render('stand/show.html.twig', [
            'stand' => $stand,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stand_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stand $stand, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StandType::class, $stand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stand/edit.html.twig', [
            'stand' => $stand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stand_delete', methods: ['POST'])]
    public function delete(Request $request, Stand $stand, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stand->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stand);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stand_index', [], Response::HTTP_SEE_OTHER);
    }
}
