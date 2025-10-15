<?php

namespace App\Controller\Admin;

use App\Entity\Concert;
use App\Form\ConcertType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConcertController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/concert', name: 'app_admin_concert', methods: ['GET'])]
    public function index(): Response
    {
        $concerts = $this->entityManager->getRepository(Concert::class)->findAll();

        return $this->render('admin/concert/index.html.twig', [
            'concerts' => $concerts,
        ]);
    }

    #[Route('/admin/concert/create', name: 'app_admin_concert_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $concert = new Concert();

        $form = $this->createForm(ConcertType::class, $concert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($concert);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_concert_read', [
                'id' => $concert->getId(),
            ]);
        }

        return $this->render('admin/concert/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/concert/{id}', name: 'app_admin_concert_read', methods: ['GET'])]
    public function read(int $id): Response
    {
        $concert = $this->entityManager->getRepository(Concert::class)->find($id);

        if ($concert === null) {
            $this->createNotFoundException('Concert not found');
        }

        return $this->render('admin/concert/read.html.twig', [
            'concert' => $concert,
        ]);
    }

    #[Route('/admin/concert/{id}/update', name: 'app_admin_concert_update', methods: ['GET', 'PUT'])]
    public function update(int $id, Request $request): Response
    {
        $concert = $this->entityManager->getRepository(Concert::class)->find($id);

        if ($concert === null) {
            throw $this->createNotFoundException('Concert not found');
        }

        $form = $this->createForm(ConcertType::class, $concert, [
            'method' => 'PUT'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_concert_read', [
                'id' => $concert->getId(),
            ]);
        }

        return $this->render('admin/concert/update.html.twig', [
            'concert' => $concert,
            'form' => $form,
        ]);
    }

    #[Route('/admin/concert/{id}/delete', name: 'app_admin_concert_delete', methods: ['GET', 'POST'])]
    public function delete(int $id, Request $request): Response
    {
        $concert = $this->entityManager->getRepository(Concert::class)->find($id);

        if ($concert === null) {
            throw $this->createNotFoundException('Concert not found');
        }

        if ($this->isCsrfTokenValid('delete'.$concert->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($concert);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_concert', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/concert/delete.html.twig', [
            'concert' => $concert,
        ]);
    }
}
