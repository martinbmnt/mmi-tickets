<?php

namespace App\Controller\API\v1;

use App\Entity\Concert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', name: 'api_v1_')]
final class ConcertController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/concerts', name: 'concerts_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('Server', 'mmiTickets');

        $response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);

        $response->setData(['hello' => 'world']);

        return $response;
    }

    #[Route('/concerts', name: 'concerts_create', methods: ['POST'])]
    public function create(): Response
    {
        $response = new Response();
        $response->headers->set('server', 'mmiTickets');

        $response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);

        return $response;
    }

    #[Route('/concerts/{id}', name: 'concerts_read', methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('Server', 'mmiTickets');

        /** @var Concert|null $concert */
        $concert = $this->entityManager->getRepository(Concert::class)->find($id);

        if (!$concert) {
            $response->getStatusCode(Response::HTTP_NOT_FOUND);

            return $response;
        }

        $response->setData([
            'music_group' => $concert->getMusicGroup(),
            'city' => $concert->getCity(),
            'country' => $concert->getCountry(),
        ]);

        return $response;
    }

    #[Route('/concerts/{id}', name: 'concerts_update', methods: ['PUT'])]
    public function update(int $id): Response
    {
        $response = new Response();
        $response->headers->set('server', 'mmiTickets');

        $concert = $this->entityManager->getRepository(Concert::class)->find($id);

        if (!$concert) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);

            return $response;
        }

        $response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);

        return $response;
    }

    #[Route('/concerts/{id}', name: 'concerts_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $response = new Response();
        $response->headers->set('server', 'mmiTickets');

        $concert = $this->entityManager->getRepository(Concert::class)->find($id);

        if (!$concert) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);

            return $response;
        }

        $response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);

        return $response;
    }

    private function getContent(Request $request): InputBag
    {
        return match ($request->headers->get('content-type')) {
            'application/json' => $request->getPayload(),
            'application/x-www-form-urlencoded' => $request->request,
            default => throw new BadRequestException()
        };
    }
}
