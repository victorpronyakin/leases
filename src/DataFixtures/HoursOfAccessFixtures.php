<?php

namespace App\DataFixtures;

use App\Entity\HoursOfAccess;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class HoursOfAccessFixtures
 * @package App\DataFixtures
 */
class HoursOfAccessFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = [ '24/7', 'Mon-Fri 08:00-17:00', 'Must call 4 hours before'];

        foreach ($types as $type){
            $contactType = new HoursOfAccess($type);
            $manager->persist($contactType);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 11;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicSite'];
    }
}
