<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Concert;
use App\Entity\Reservation;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class QRCodeGenerator {
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly HttpClientInterface $spotifyClient
    ) {
    }

    public function generate(Reservation $reservation): ResultInterface
    {
        $writer = new PngWriter();

        $url = $this->urlGenerator->generate(
            'api_v1_reservations_read',
            ['id' => $reservation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $qrCode = new QrCode(
            data: $url,
            size: 600,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium
        );

        $image = $this->getImage($reservation->getConcert());

        if ($image) {
            $logo = new Logo(
                path: $image,
                resizeToWidth: 126,
                punchoutBackground: true
            );
        }

        $label = new Label($reservation->getReference());

        return $writer->write($qrCode, $logo ?? null, $label);
    }

    private function getImage(Concert $concert): ?string
    {
        $response = $this->spotifyClient->request(
            Request::METHOD_GET,
            "https://api.spotify.com/v1/search",
            [
                'query' => [
                    'q' => $concert->getMusicGroup(),
                    'type' => 'artist',
                    'limit' => 1,
                    'market' => 'FR',
                ]
            ]
        );

        $data = json_decode($response->getContent());

        if (count($data->artists->items) < 1) {
            return null;
        }

        return $data->artists->items[0]->images[0]->url;
    }
}
