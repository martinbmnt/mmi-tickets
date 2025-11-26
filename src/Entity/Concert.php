<?php

namespace App\Entity;

use App\Repository\ConcertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConcertRepository::class)]
class Concert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    private ?string $musicGroup;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $places;

    #[ORM\Column(length: 255)]
    private ?string $city = 'Laval';

    #[ORM\Column(length: 255)]
    private ?string $country = 'France';

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'concert', orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '/concerts/' . $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMusicGroup(): string
    {
        return $this->musicGroup;
    }

    public function setMusicGroup(string $musicGroup): void
    {
        $this->musicGroup = $musicGroup;
    }

    public function getPlaces(): int
    {
        return $this->places;
    }

    public function setPlaces(int $places): void
    {
        $this->places = $places;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): void
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setConcert($this);
        }
    }

    public function removeReservation(Reservation $reservation): void
    {
        $this->reservations->removeElement($reservation);
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }
}
