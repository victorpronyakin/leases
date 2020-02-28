<?php

namespace App\DataFixtures;

use App\Entity\LandlordDocumentStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LandlordDocumentStatusFixtures
 * @package App\DataFixtures
 */
class LandlordDocumentStatusFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $statuses = ['Complete', 'Missing Documents (Essential)', 'Missing documents (non-essential)'];

        foreach ($statuses as $status){
            $landlordDocumentStatus = new LandlordDocumentStatus($status);
            $manager->persist($landlordDocumentStatus);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 10;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLandlord'];
    }
}
