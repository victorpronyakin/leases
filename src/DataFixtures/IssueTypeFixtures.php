<?php

namespace App\DataFixtures;

use App\Entity\IssueType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class IssueTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Access', 'Billing', 'Additional Antennas', 'Theft', 'Electricity'];

        foreach ($types as $type){
            $type = new IssueType($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 20;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicIssue'];
    }
}
