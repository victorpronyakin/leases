<?php


namespace App\DataFixtures;


use App\Entity\UserType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserFixtures
 * @package App\DataFixtures
 */
class UserFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserPasswordEncoderInterface $encoder, UserManagerInterface $userManager)
    {
        $this->encoder = $encoder;
        $this->userManager = $userManager;
    }

    public function load(ObjectManager $manager)
    {
        // User Types
        $typeTenant = $manager->getRepository(UserType::class)->findOneBy(['name'=>'Tenant']);
        $typeAgent = $manager->getRepository(UserType::class)->findOneBy(['name'=>'Agent']);
        // Super Admin
        $superAdmin = $this->userManager->createUser();
        $superAdmin->setUsername('admin@gmail.com');
        $superAdmin->setEmail('admin@gmail.com');
        $password = $this->encoder->encodePassword($superAdmin, 'admin');
        $superAdmin->setPassword($password);
        $superAdmin->addRole('ROLE_SUPER_ADMIN');
        $superAdmin->setFirstName('Super');
        $superAdmin->setLastName('Admin');
        $superAdmin->setPhone('+380999999999');
        $superAdmin->setCompany('Admin');
        $superAdmin->setDesignation('Designation Super Admin');
        $superAdmin->setType($typeTenant);
        $this->userManager->updateUser($superAdmin);
        // Tenant
        $tenant = $this->userManager->createUser();
        $tenant->setUsername('tenant@gmail.com');
        $tenant->setEmail('tenant@gmail.com');
        $password = $this->encoder->encodePassword($tenant, 'tenant');
        $tenant->setPassword($password);
        $tenant->addRole('ROLE_ADMIN');
        $tenant->setFirstName('Tenant');
        $tenant->setLastName('User');
        $tenant->setPhone('+380999999998');
        $tenant->setCompany('Tenant');
        $tenant->setDesignation('Designation Tenant');
        $tenant->setType($typeTenant);
        $this->userManager->updateUser($tenant);
        // Agent
        $agent = $this->userManager->createUser();
        $agent->setUsername('agent@gmail.com');
        $agent->setEmail('agent@gmail.com');
        $password = $this->encoder->encodePassword($agent, 'agent');
        $agent->setPassword($password);
        $agent->addRole('ROLE_ADMIN');
        $agent->setFirstName('Agent');
        $agent->setLastName('User');
        $agent->setPhone('+380999999997');
        $agent->setCompany('Agent');
        $agent->setDesignation('Designation Agent');
        $agent->setType($typeAgent);
        $this->userManager->updateUser($agent);
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 2;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['User'];
    }
}
