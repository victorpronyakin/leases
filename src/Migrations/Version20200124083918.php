<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200124083918 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site ADD primary_emergency_contact_id INT DEFAULT NULL, ADD secondary_emergency_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E425154A26 FOREIGN KEY (primary_emergency_contact_id) REFERENCES landlord_contact (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E427F4AEF7 FOREIGN KEY (secondary_emergency_contact_id) REFERENCES landlord_contact (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_694309E425154A26 ON site (primary_emergency_contact_id)');
        $this->addSql('CREATE INDEX IDX_694309E427F4AEF7 ON site (secondary_emergency_contact_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E425154A26');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E427F4AEF7');
        $this->addSql('DROP INDEX IDX_694309E425154A26 ON site');
        $this->addSql('DROP INDEX IDX_694309E427F4AEF7 ON site');
        $this->addSql('ALTER TABLE site DROP primary_emergency_contact_id, DROP secondary_emergency_contact_id');
    }
}
