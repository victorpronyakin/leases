<?php

namespace App\DataFixtures;

use App\Entity\LeaseDepositType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LeaseDepositTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Bank Guarantee', 'Landlord (interest bearing)', 'Landlord (non-interest bearing)'];

        foreach ($types as $type){
            $type = new LeaseDepositType($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 14;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLease'];
    }
}
