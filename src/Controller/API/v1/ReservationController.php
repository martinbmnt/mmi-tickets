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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1', name: 'api_v1_')]
final class ReservationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/reservations', name: 'reservations_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('server', 'mmiTickets');

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($page < 1 || $limit < 1) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            $response->setData([
                'error' => "invalid page or limit query",
            ]);

            return $response;
        }

        $reservationRepository = $this->entityManager->getRepository(Reservation::class);
        $reservationsCount = $reservationRepository->count();

        $links = [];

        if ($reservationsCount > 0) {
            $firstUrl = $this->generateUrl('api_v1_reservations_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $links[] = "<$firstUrl>; rel=\"first\"";

            $latestUrl = $this->generateUrl('api_v1_reservations_read_latest', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $links[] = "<$latestUrl>; rel=\"last\"";
        }

        // Out of range page index.
        if ($page > ceil($reservationsCount / $limit)) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);

            if (!empty($links)) {
                $response->headers->set('Links', $links);
            }

            return $response;
        }

        if ($limit > $reservationsCount) {
            $limit = $reservationsCount;
        }

        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy([], null, $limit, ($page - 1) * $limit);

        $response->headers->set('Content-Range', 'urls ' . ($page * $limit - ($limit - 1)) . '-' . min($reservationsCount, $page * $limit) . '/' . $reservationsCount);
        $response->headers->set('X-Total-Count', $reservationsCount);
        $response->headers->set('X-Page-Size', count($reservations));
        $response->headers->set('X-Current-Page', $page);

        if ($page > 1) {
            $previousUrl = $this->generateUrl('api_v1_reservations_index', ['page' => $page - 1], UrlGeneratorInterface::ABSOLUTE_URL);
            $links[] = "<$previousUrl>; rel=\"prev\"";
        }

        if ($reservationsCount > (count($reservations) + ($page - 1) * $limit)) {
            $nextUrl = $this->generateUrl('api_v1_reservations_index', ['page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL);
            $links[] = "<$nextUrl>; rel=\"next\"";
        }

        if (!empty($links)) {
            $response->headers->set('Links', $links);
        }

        $response->setStatusCode(Response::HTTP_OK);

        // Process response data.

        $reservationsLocations = [];

        foreach ($reservations as $reservation) {
            $reservationsLocations[] = $this->generateUrl(
                'api_v1_reservations_read',
                ['id' => $reservation->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $response->setData([
            'locations' => $reservationsLocations,
            'meta' => [
                'total_count' => count($reservationsLocations),
            ],
        ]);

        return $response;
    }

    #[Route('/reservations', name: 'reservations_create', methods: ['POST'])]
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

        $response->headers->set(
            'Location',
            $this->generateUrl(
                'api_v1_reservations_read',
                ['id' => $reservation->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        return $response;
    }

    #[Route('/reservations/{id}', name: 'reservations_read', methods: ['GET'])]
    public function read(int $id, SerializerInterface $serializer): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('server', 'mmiTickets');

        // Récupérer la réservation selon l'id

        /** @var Reservation|null $reservation */
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setData([
                'error' => 'reservation not found',
            ]);

            return $response;
        }

        // Formater la réservation en JSON

        $reservationJson = $serializer->serialize($reservation, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                'pseudo',
                'status',
                'concertReference', // Appelle la méthode Reservation::getConcertReference
            ]
        ]);

        $response->setContent($reservationJson);

        // Ajouter les entêtes spécifiques : QR Code, Concert, Collection

        $reservationQrCodeUrl = $this->generateUrl(
            'api_v1_reservations_qrcode',
            ['id' => $reservation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ); // exemple d'url générée : http://127.0.0.1:8000/api/v1/reservations/21/qrcode

        $concertTitle = "Concert - {$reservation->getConcert()->getMusicGroup()}";
        $concertReadUrl = $this->generateUrl(
            'api_v1_concerts_read',
            ['id' => $reservation->getConcert()->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ); // exemple d'url générée : http://127.0.0.1:8000/api/v1/concerts/5

        $reservationsIndexUrl = $this->generateUrl(
            'api_v1_reservations_index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $links = [
            "<{$reservationQrCodeUrl}>; title=\"QR code\"; type=\"image/png\"", // QR Code
            "<{$concertReadUrl}>; rel=\"related\"; title=\"$concertTitle\"", // Concert read
            "<{$reservationsIndexUrl}>; rel=\"collection\";", // Reservations index
        ];

        $response->headers->set('Link', $links);

        return $response;
    }

    #[Route('/reservations/latest', name: 'reservations_read_latest', methods: ['GET'], priority: 1)]
    public function latest(SerializerInterface $serializer): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('server', 'mmiTickets');

        $reservation = $this->entityManager->getRepository(Reservation::class)->findOneBy([], ['id' => 'desc']);

        if (!$reservation) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);

            return $response;
        }

        $reservationJson = $serializer->serialize($reservation, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                'pseudo',
                'status',
                'concertReference',
            ]
        ]);

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($reservationJson);

        // Ajouter les entêtes spécifiques : QR Code, Concert, Collection

        $reservationQrCodeUrl = $this->generateUrl(
            'api_v1_reservations_qrcode',
            ['id' => $reservation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ); // exemple d'url générée : http://127.0.0.1:8000/api/v1/reservations/21/qrcode

        $concertTitle = "Concert - {$reservation->getConcert()->getMusicGroup()}";
        $concertReadUrl = $this->generateUrl(
            'api_v1_concerts_read',
            ['id' => $reservation->getConcert()->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ); // exemple d'url générée : http://127.0.0.1:8000/api/v1/concerts/5

        $reservationsIndexUrl = $this->generateUrl(
            'api_v1_reservations_index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $links = [
            "<{$reservationQrCodeUrl}>; title=\"QR code\"; type=\"image/png\"", // QR Code
            "<{$concertReadUrl}>; rel=\"related\"; title=\"$concertTitle\"", // Concert read
            "<{$reservationsIndexUrl}>; rel=\"collection\";", // Reservations index
        ];

        $response->headers->set('Link', $links);

        return $response;
    }

    #[Route('/reservations/{id}', name: 'reservations_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('server', 'mmiTickets');

        // Récupérer la réservation selon l'id

        /** @var Reservation|null $reservation */
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setData([
                'error' => 'reservation not found',
            ]);

            return $response;
        }

        // Vérification des données soumises

        if (!$request->request->count()) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST, "missing update fields");

            $response->setData([
                'error' => "pseudo or status must not be empty",
            ]);

            return $response;
        }

        // Mettre à jour la réservation à partir des données soumises

        $pseudo = $request->request->get('pseudo');
        $status = $request->request->get('status');

        if ($status) {
            try {
                $reservation->setStatus(ReservationStatus::from($status));
            } catch (\ValueError|\TypeError $e) {
                $response->setStatusCode(Response::HTTP_BAD_REQUEST, "invalid status value");

                $response->setData([
                    'error' => "invalid value for status, valid values are 'confirmed' and 'pending'",
                ]);

                return $response;
            }
        }

        if ($pseudo) {
            $reservation->setPseudo($pseudo);
        }

        $this->entityManager->flush();

        $response->setStatusCode(Response::HTTP_OK, 'Content updated');

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
