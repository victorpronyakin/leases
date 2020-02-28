<?php

namespace App\DataFixtures;

use App\Entity\SiteInventoryCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class SiteInventoryCategoryFixtures
 * @package App\DataFixtures
 */
class SiteInventoryCategoryFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = [ 'CONTAINER', '600MM MW', '300MMW', '1200MM MW', 'PTMP ANTENNA', 'LTE ANTENNA', 'FIBRE RETICULATION', 'BATTERIES', 'GENERATOR'];

        foreach ($types as $type){
            $contactType = new SiteInventoryCategory($type);
            $manager->persist($contactType);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 12;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicSite'];
    }
}
