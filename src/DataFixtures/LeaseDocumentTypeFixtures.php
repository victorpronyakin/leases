<?php

namespace App\DataFixtures;

use App\Entity\LeaseDocumentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LeaseDocumentTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['New Lease Agreement', 'Lease Renewal Agreement', 'Lease Addendum', 'Site Drawings'];

        foreach ($types as $type){
            $type = new LeaseDocumentType($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 15;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLease'];
    }
}
