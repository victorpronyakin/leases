<?php

namespace App\DataFixtures;

use App\Entity\ManagementStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ManagementStatusFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Active', 'Can be renewed', 'No renewal', 'Under Investigation', 'Negotiation in progress', 'Terms Agreed', 'Awaiting Landlord Signature', 'Awaiting Tenant Signature', 'Renewed'];

        foreach ($types as $type){
            $type = new ManagementStatus($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 19;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['SiteStatus'];
    }
}
