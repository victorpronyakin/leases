<?php

namespace App\DataFixtures;

use App\Entity\LeaseType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LeaseTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Basement', 'Rooftop', 'Tower', 'Offices', 'Data-Centre', 'Retail'];

        foreach ($types as $type){
            $type = new LeaseType($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 18;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLease'];
    }
}
