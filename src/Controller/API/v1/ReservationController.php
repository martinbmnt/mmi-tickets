<?php

namespace App\Controller\API\v1;

use App\Entity\Concert;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', name: 'api_v1_')]
final class ReservationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/reservations', name: 'reservations_create_correction', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('server', 'mmiTickets');

        $pseudo = $request->request->get('pseudo');
        $concertRef = $request->request->get('concert');

        if (!isset($pseudo) || !isset($concertRef)) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST, "pseudo and concert must not be empty");
            $response->setData([
                'error' => "pseudo and concert must not be empty",
            ]);

            return $response;
        }

        if (!preg_match('/\/concerts\/\d+/', $concertRef)) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST, "invalid concert");
            $response->setData([
                'error' => "invalid concert",
            ]);

            return $response;
        }

        $concertId = preg_replace('/\/concerts\//', '', $concertRef);

        $concert = $this->entityManager->getRepository(Concert::class)->find($concertId);

        if (!$concert) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST, "invalid concert");
            $response->setData([
                'error' => "invalid concert",
            ]);

            return $response;
        }

        $reservation = new Reservation();
        $reservation->setPseudo($pseudo);
        $reservation->setConcert($concert);

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        $response->setStatusCode(Response::HTTP_CREATED, 'Created');

        // todo: générer l'url de la réservation.
        $response->headers->set('Location', 'url de la nouvelle reservation');


        return $response;
    }

    #[Route('/reservations/{id}/qrcode', name: 'reservations_qrcode', methods: ['GET'])]
    public function qrcode(int $id): Response
    {
        $response = new JsonResponse();
        $response->headers->set('server', 'mmiTickets');

        // Récupérer la réservation selon l'id

        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setData([
                'error' => 'reservation not found',
            ]);

            return $response;
        }

        // Générer le QR Code

        $response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);

        return $response;
    }
}
