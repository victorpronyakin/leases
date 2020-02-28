<?php

namespace App\DataFixtures;

use App\Entity\BEEStatusType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class BEEStatusTypeFixtures
 * @package App\DataFixtures
 */
class BEEStatusTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Not registered', 'Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5'];

        foreach ($types as $type){
            $contactType = new BEEStatusType($type);
            $manager->persist($contactType);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 8;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLandlord'];
    }
}
