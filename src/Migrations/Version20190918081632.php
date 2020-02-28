<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190918081632 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE management_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lease ADD management_status_id INT DEFAULT NULL, ADD management_status_updated DATETIME DEFAULT NULL, ADD terminate_status TINYINT(1) DEFAULT NULL, ADD terminate_date DATETIME DEFAULT NULL, CHANGE management_status agent_status TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495F7C87ED9 FOREIGN KEY (management_status_id) REFERENCES management_status (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E6C77495F7C87ED9 ON lease (management_status_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C77495F7C87ED9');
        $this->addSql('DROP TABLE management_status');
        $this->addSql('DROP INDEX IDX_E6C77495F7C87ED9 ON lease');
        $this->addSql('ALTER TABLE lease ADD management_status TINYINT(1) DEFAULT NULL, DROP management_status_id, DROP agent_status, DROP management_status_updated, DROP terminate_status, DROP terminate_date');
    }
}
