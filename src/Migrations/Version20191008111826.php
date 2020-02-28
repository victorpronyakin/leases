<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Permission;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191008111826 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $em = $this->container->get('doctrine.orm.entity_manager');

        $permissions = [
            [
                'name' => 'Lease',
                'view' => true,
                'add' => true,
                'edit' => true,
                'remove' => true,
            ],
            [
                'name' => 'Site',
                'view' => true,
                'add' => true,
                'edit' => true,
                'remove' => true,
            ],
            [
                'name' => 'Landlord',
                'view' => true,
                'add' => true,
                'edit' => true,
                'remove' => true,
            ],
            [
                'name' => 'Financial',
                'view' => true,
                'add' => false,
                'edit' => true,
                'remove' => false,
            ],
            [
                'name' => 'Agent',
                'view' => true,
                'add' => true,
                'edit' => true,
                'remove' => true,
            ],
            [
                'name' => 'Agent Saving',
                'view' => true,
                'add' => false,
                'edit' => false,
                'remove' => false,
            ],
            [
                'name' => 'Agent Billing',
                'view' => true,
                'add' => false,
                'edit' => true,
                'remove' => false,
            ],
            [
                'name' => 'Agent User',
                'view' => true,
                'add' => false,
                'edit' => false,
                'remove' => false,
            ],
            [
                'name' => 'Issue',
                'view' => true,
                'add' => true,
                'edit' => true,
                'remove' => true,
            ],
            [
                'name' => 'Setting',
                'view' => true,
                'add' => false,
                'edit' => true,
                'remove' => false,
            ],
            [
                'name' => 'Report',
                'view' => true,
                'add' => false,
                'edit' => false,
                'remove' => false,
            ],
        ];

        foreach ($permissions as $permissionItem){
            $permission = new Permission($permissionItem['name'], $permissionItem['view'], $permissionItem['add'], $permissionItem['edit'], $permissionItem['remove']);
            $em->persist($permission);
        }

        $em->flush();

        $this->addSql('SELECT 1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE TABLE permission');
    }
}
