<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/reservation', name: 'app_admin_reservation', methods: ['GET'])]
    public function index(): Response
    {
        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/admin/reservation/create', name: 'app_admin_reservation_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $reservation = new Reservation();

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_reservation_read', [
                'id' => $reservation->getId(),
            ]);
        }

        return $this->render('admin/reservation/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/reservation/{id}', name: 'app_admin_reservation_read', methods: ['GET'])]
    public function read(int $id): Response
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if ($reservation === null) {
            $this->createNotFoundException('Reservation not found');
        }

        return $this->render('admin/reservation/read.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/admin/reservation/{id}/update', name: 'app_admin_reservation_update', methods: ['GET', 'PUT'])]
    public function update(int $id, Request $request): Response
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if ($reservation === null) {
            $this->createNotFoundException('Reservation not found');
        }

        $form = $this->createForm(ReservationType::class, $reservation, [
            'method' => 'PUT',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_reservation_read', [
                'id' => $reservation->getId(),
            ]);
        }

        return $this->render('admin/reservation/update.html.twig', [
            'form' => $form,
            'reservation' => $reservation,
        ]);
    }

    #[Route('/admin/reservation/{id}/delete', name: 'app_admin_reservation_delete', methods: ['GET', 'POST'])]
    public function delete(int $id, Request $request): Response
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if ($reservation === null) {
            throw $this->createNotFoundException('Reservation not found');
        }

        if ($reservation->isConfirmed()) {
            return $this->redirectToRoute('app_admin_reservation', [], Response::HTTP_FORBIDDEN);
        }

        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($reservation);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_reservation', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservation/delete.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
