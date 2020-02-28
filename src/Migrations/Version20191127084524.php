<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\ContactReminder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127084524 extends AbstractMigration implements ContainerAwareInterface
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

        /**
         * @var EntityManager $em
         */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $emergencyContactReminder = new ContactReminder(2, 3, 'Dear {NAME}, you are currently the EMERGENCY CONTACT at {SITE_NUMBER}-{SITE_NAME}, {SITE_ADDRESS}, {SITE_CITY}. We have the following information on the system: {NAME}, {SURNAME}, {EMAIL}, {CELL}.',true, true, true, 'info@exprop.co.za', true);
        $em->persist($emergencyContactReminder);
        $allContactReminder = new ContactReminder(1, 4, 'Dear {NAME}, you are currently the CONTACT at EXPROP. We have the following information on the system: {NAME}, {SURNAME}, {EMAIL}, {CELL}.',true, true, false, 'info@exprop.co.za', true);
        $em->persist($allContactReminder);
        $em->flush();

        $this->addSql('Select 1;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE TABLE contact_reminder');
    }
}
