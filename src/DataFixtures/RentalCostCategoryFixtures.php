<?php

namespace App\DataFixtures;

use App\Entity\RentalCostCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class RentalCostCategoryFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Gross Rental', 'Base Cost', 'Ground Space', 'Operating Costs', 'Access Costs', 'Container', 'MW Antenna (300mm)', 'MW Antenna (600m)', 'MW Antenna (1200mm)', '300mm PTMP Antenna' ];

        foreach ($types as $type){
            $type = new RentalCostCategory($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 13;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLease'];
    }
}
