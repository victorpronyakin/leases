<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190917085756 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C7749579CF281C');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C7749579CF281C FOREIGN KEY (document_status_id) REFERENCES landlord_document_status (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C7749579CF281C');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C7749579CF281C FOREIGN KEY (document_status_id) REFERENCES lease_document_type (id) ON DELETE SET NULL');
    }
}
