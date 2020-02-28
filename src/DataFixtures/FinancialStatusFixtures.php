<?php

namespace App\DataFixtures;

use App\Entity\FinancialStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FinancialStatusFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = ['Invoice Received', 'Invoice Sent For Payment', 'Invoice paid', 'Paid without invoice'];

        foreach ($types as $type){
            $type = new FinancialStatus($type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 21;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['BasicFinancial'];
    }
}
