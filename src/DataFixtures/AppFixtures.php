<?php

namespace App\DataFixtures;

use App\Entity\Concert;
use App\Entity\Reservation;
use App\Enum\ReservationStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $concertHermanDune = new Concert();
        $concertHermanDune->setMusicGroup("Herman Dune");
        $concertHermanDune->setDate(new \DateTimeImmutable("2025-10-12"));
        $concertHermanDune->setPlaces(250);
        $concertHermanDune->setCity("Laval");
        $concertHermanDune->setCity("France");
        $manager->persist($concertHermanDune);

        $concertVendrediSurMer = new Concert();
        $concertVendrediSurMer->setMusicGroup("Vendredi sur Mer");
        $concertVendrediSurMer->setDate(new \DateTimeImmutable("2025-10-17"));
        $concertVendrediSurMer->setPlaces(250);
        $concertVendrediSurMer->setCity("Laval");
        $concertVendrediSurMer->setCity("France");
        $manager->persist($concertVendrediSurMer);

        $manager->flush();

        $reservationHemannDune = new Reservation();
        $reservationHemannDune->setConcert($concertHermanDune);
        $reservationHemannDune->setPseudo('Martin');
        $reservationHemannDune->setStatus(ReservationStatus::Confirmed);
        $manager->persist($reservationHemannDune);

        $reservationHemannDune2 = new Reservation();
        $reservationHemannDune2->setConcert($concertHermanDune);
        $reservationHemannDune2->setPseudo('Martdeux');
        $manager->persist($reservationHemannDune2);

        $reservationVendrediSurMer = new Reservation();
        $reservationVendrediSurMer->setConcert($concertVendrediSurMer);
        $reservationVendrediSurMer->setPseudo('Martin');
        $reservationVendrediSurMer->setStatus(ReservationStatus::Confirmed);
        $manager->persist($reservationVendrediSurMer);

        $reservationVendrediSurMer2 = new Reservation();
        $reservationVendrediSurMer2->setConcert($concertVendrediSurMer);
        $reservationVendrediSurMer2->setPseudo('Martdeux');
        $manager->persist($reservationVendrediSurMer2);

        $reservationVendrediSurMer3 = new Reservation();
        $reservationVendrediSurMer3->setConcert($concertVendrediSurMer);
        $reservationVendrediSurMer3->setPseudo('Marttrois');
        $manager->persist($reservationVendrediSurMer3);

        $manager->flush();
    }
}
