<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190911072502 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE landlord_document DROP FOREIGN KEY FK_226A8B0C6BF700BD');
        $this->addSql('DROP INDEX IDX_226A8B0C6BF700BD ON landlord_document');
        $this->addSql('ALTER TABLE landlord_document DROP status_id');
        $this->addSql('ALTER TABLE landlord ADD document_status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE landlord ADD CONSTRAINT FK_F446E8F879CF281C FOREIGN KEY (document_status_id) REFERENCES landlord_document_status (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F446E8F879CF281C ON landlord (document_status_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE landlord DROP FOREIGN KEY FK_F446E8F879CF281C');
        $this->addSql('DROP INDEX IDX_F446E8F879CF281C ON landlord');
        $this->addSql('ALTER TABLE landlord DROP document_status_id');
        $this->addSql('ALTER TABLE landlord_document ADD status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE landlord_document ADD CONSTRAINT FK_226A8B0C6BF700BD FOREIGN KEY (status_id) REFERENCES landlord_document_status (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_226A8B0C6BF700BD ON landlord_document (status_id)');
    }
}
