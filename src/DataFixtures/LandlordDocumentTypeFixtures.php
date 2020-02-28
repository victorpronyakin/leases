<?php

namespace App\DataFixtures;

use App\Entity\LandlordDocumentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LandlordDocumentTypeFixtures
 * @package App\DataFixtures
 */
class LandlordDocumentTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Company Registration Documents', 'ID Documents', 'Company resolution for signatory', 'Proof of ownership of property', 'Tax Clearance Certificate', 'VAT Registration Certificate', 'BEE Certificate', 'Trust Deeds'];

        foreach ($types as $type){
            $contactType = new LandlordDocumentType($type);
            $manager->persist($contactType);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 9;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicLandlord'];
    }
}
