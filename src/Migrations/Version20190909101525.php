<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190909101525 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE landlord_document (id INT AUTO_INCREMENT NOT NULL, landlord_id INT NOT NULL, type_id INT NOT NULL, status_id INT DEFAULT NULL, document VARCHAR(255) NOT NULL, INDEX IDX_226A8B0CD48E7AED (landlord_id), INDEX IDX_226A8B0CC54C8C93 (type_id), INDEX IDX_226A8B0C6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE landlord (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, country_id INT DEFAULT NULL, bee_status_id INT DEFAULT NULL, number VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, address1 VARCHAR(255) DEFAULT NULL, address2 VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, account_holder VARCHAR(255) DEFAULT NULL, bank_name VARCHAR(255) DEFAULT NULL, branch_name VARCHAR(255) DEFAULT NULL, branch_code VARCHAR(255) DEFAULT NULL, account_number VARCHAR(255) DEFAULT NULL, account_type VARCHAR(255) DEFAULT NULL, bank_document VARCHAR(255) DEFAULT NULL, vat_status TINYINT(1) DEFAULT NULL, vat_number VARCHAR(255) DEFAULT NULL, bee_expiry DATETIME DEFAULT NULL, comments LONGTEXT DEFAULT NULL, created DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, INDEX IDX_F446E8F8C54C8C93 (type_id), INDEX IDX_F446E8F8F92F3E70 (country_id), INDEX IDX_F446E8F83548C123 (bee_status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE landlord_document ADD CONSTRAINT FK_226A8B0CD48E7AED FOREIGN KEY (landlord_id) REFERENCES landlord (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE landlord_document ADD CONSTRAINT FK_226A8B0CC54C8C93 FOREIGN KEY (type_id) REFERENCES landlord_document_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE landlord_document ADD CONSTRAINT FK_226A8B0C6BF700BD FOREIGN KEY (status_id) REFERENCES landlord_document_status (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE landlord ADD CONSTRAINT FK_F446E8F8C54C8C93 FOREIGN KEY (type_id) REFERENCES landlord_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE landlord ADD CONSTRAINT FK_F446E8F8F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE landlord ADD CONSTRAINT FK_F446E8F83548C123 FOREIGN KEY (bee_status_id) REFERENCES beestatus_type (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE landlord_document DROP FOREIGN KEY FK_226A8B0CD48E7AED');
        $this->addSql('DROP TABLE landlord_document');
        $this->addSql('DROP TABLE landlord');
    }
}
