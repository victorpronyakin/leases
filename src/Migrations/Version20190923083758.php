<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190923083758 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C77495F7C87ED9');
        $this->addSql('DROP INDEX IDX_E6C77495F7C87ED9 ON lease');
        $this->addSql('ALTER TABLE lease DROP management_status_id, DROP management_status_updated');
        $this->addSql('ALTER TABLE site ADD site_status_id INT DEFAULT NULL, ADD site_status_updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4DFD76C8 FOREIGN KEY (site_status_id) REFERENCES management_status (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_694309E4DFD76C8 ON site (site_status_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease ADD management_status_id INT DEFAULT NULL, ADD management_status_updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495F7C87ED9 FOREIGN KEY (management_status_id) REFERENCES management_status (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E6C77495F7C87ED9 ON lease (management_status_id)');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4DFD76C8');
        $this->addSql('DROP INDEX IDX_694309E4DFD76C8 ON site');
        $this->addSql('ALTER TABLE site DROP site_status_id, DROP site_status_updated');
    }
}
