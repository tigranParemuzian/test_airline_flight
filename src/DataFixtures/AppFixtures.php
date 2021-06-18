<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Flight;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        for ($i = 0; $i < 20; $i++) {

            $now = new \DateTime('now');
            $flight = new Flight();
            $flight->setName('Flight_# ' . $this->generateRandomString(5));

            $flight->setSeatCount(mt_rand(1, 150));

            $fromAddress = new Address();
            $fromAddress->setState('Los Angeles');
            $fromAddress->setCity($this->getRendomCity()['Los Angeles'][mt_rand(0, 86)]);
            $fromAddress->setZipCode((string)mt_rand(100, 10000));


            $flight->setFromLocation($fromAddress);
            $flight->setToLocation(clone $fromAddress);
            $flight->getToLocation()->setCity($this->getRendomCity()['Los Angeles'][mt_rand(0, 86)]);

            $flight->setCost(mt_rand(1000, 3000));

            $flight->setStartedAt((clone $now)->modify(sprintf('+%s hours', $i)));
            $flight->setFinishedAt((clone $flight->getStartedAt())->modify(sprintf('+%s hours', $i+ mt_rand(1,24))));
            $manager->persist($flight);
        }

        $manager->flush();
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function getRendomCity()
    {

        return [

            'Los Angeles' => ["Agoura Hills",
                "Alhambra",
                "Arcadia",
                "Artesia",
                "Avalon",
                "Azusa",
                "Baldwin Park",
                "Bell",
                "Bellflower",
                "Bell Gardens",
                "Beverly Hills",
                "Bradbury",
                "Burbank",
                "Calabasas",
                "Carson",
                "Cerritos",
                "Claremont",
                "Commerce",
                "Compton",
                "Covina",
                "Cudahy",
                "Culver City",
                "Diamond Bar",
                "Downey",
                "Duarte",
                "El Monte",
                "El Segundo",
                "Gardena",
                "Glendale",
                "Glendora",
                "Hawaiian Gardens",
                "Hawthorne",
                "Hermosa Beach",
                "Hidden Hills",
                "Huntington Park",
                "Industry",
                "Inglewood",
                "Irwindale",
                "La Canada Flintridge",
                "La Habra Heights",
                "Lakewood",
                "La Mirada",
                "Lancaster",
                "La Puente",
                "La Verne",
                "Lawndale",
                "Lomita",
                "Long Beach",
                "Los Angeles",
                "Lynwood",
                "Malibu",
                "Manhattan Beach",
                "Maywood",
                "Monrovia",
                "Montebello",
                "Monterey Park",
                "Norwalk",
                "Palmdale",
                "Palos Verdes Estates",
                "Paramount",
                "Pasadena",
                "Pico Rivera",
                "Pomona",
                "Rancho Palos Verdes",
                "Redondo Beach",
                "Rolling Hills",
                "Rolling Hills Estates",
                "Rosemead",
                "San Dimas",
                "San Fernando",
                "San Gabriel",
                "San Marino",
                "Santa Clarita",
                "Santa Fe Springs",
                "Santa Monica",
                "Sierra Madre",
                "Signal Hill",
                "South El Monte",
                "South Gate",
                "South Pasadena",
                "Temple City",
                "Torrance",
                "Vernon",
                "Walnut",
                "West Covina",
                "West Hollywood",
                "Westlake Village",
                "Whittier"]
        ];
    }
}
