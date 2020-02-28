<?php

namespace App\DataFixtures;

use App\Entity\LandlordContactType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LandlordContactTypeFixtures
 * @package App\DataFixtures
 */
class LandlordContactTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = [ 'Accounts', 'Director', 'Managing Agent', 'Owner', 'Property Manager'];

        foreach ($types as $type){
            $contactType = new LandlordContactType($type);
            $manager->persist($contactType);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 7;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLandlord'];
    }
}
