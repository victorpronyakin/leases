<?php


namespace App\DataFixtures;


use App\Entity\UserType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserTypeFixtures
 * @package App\DataFixtures
 */
class UserTypeFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userType1 = new UserType('Tenant');
        $manager->persist($userType1);
        $userType2 = new UserType('Agent');
        $manager->persist($userType2);
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 1;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['User'];
    }
}
