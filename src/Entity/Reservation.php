<?php

namespace App\Entity;

use App\Enum\ReservationStatus;
use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private Concert $concert;

    #[ORM\Column(enumType: ReservationStatus::class)]
    private ReservationStatus $status;

    #[ORM\Column(length: 255)]
    private string $pseudo;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $confirmedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = ReservationStatus::Pending;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return '#' . $this->getCreatedAt()->format('Y') . '-' . sprintf("%04d", $this->getId());
    }

    public function getConcert(): Concert
    {
        return $this->concert;
    }

    public function setConcert(Concert $concert): void
    {
        $this->concert = $concert;
    }

    public function isConfirmed(): bool
    {
        return $this->status === ReservationStatus::Confirmed;
    }

    public function getStatus(): ReservationStatus
    {
        return $this->status;
    }

    public function setStatus(ReservationStatus $status): void
    {
        if ($status === ReservationStatus::Confirmed) {
            $this->confirmedAt = new \DateTimeImmutable();
        }

        $this->status = $status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    public function getConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->confirmedAt;
    }
}
