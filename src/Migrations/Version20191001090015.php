<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191001090015 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE landlord DROP FOREIGN KEY FK_F446E8F8F92F3E70');
        $this->addSql('DROP INDEX IDX_F446E8F8F92F3E70 ON landlord');
        $this->addSql('ALTER TABLE landlord ADD country VARCHAR(255) DEFAULT NULL, DROP country_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE landlord ADD country_id INT DEFAULT NULL, DROP country');
        $this->addSql('ALTER TABLE landlord ADD CONSTRAINT FK_F446E8F8F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F446E8F8F92F3E70 ON landlord (country_id)');
    }
}
