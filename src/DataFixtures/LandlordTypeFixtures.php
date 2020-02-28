<?php

namespace App\DataFixtures;

use App\Entity\LandlordType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LandlordTypeFixtures
 * @package App\DataFixtures
 */
class LandlordTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Private Company', 'Listed Company', 'Trust', 'Non-Profit Organisation', 'Individual', 'Body Corporate'];

        foreach ($types as $type){
            $landlordType = new LandlordType($type);
            $manager->persist($landlordType);
        }
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 5;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLandlord'];
    }
}
