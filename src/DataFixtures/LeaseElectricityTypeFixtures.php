<?php

namespace App\DataFixtures;

use App\Entity\LeaseElectricityType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LeaseElectricityTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Electricity (Council Direct - Metered)', 'Electricity (Landlord - Metered)', 'Electricity (Landlord - Fixed)'];

        foreach ($types as $type){
            $type = new LeaseElectricityType($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 16;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLease'];
    }
}
