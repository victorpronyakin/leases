<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190916105711 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lease_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_electricity_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease (id INT AUTO_INCREMENT NOT NULL, landlord_id INT NOT NULL, site_id INT NOT NULL, electricity_type_id INT DEFAULT NULL, deposit_type_id INT DEFAULT NULL, document_status_id INT DEFAULT NULL, type LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', sqm VARCHAR(255) DEFAULT NULL, term VARCHAR(255) DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, renewal_status TINYINT(1) DEFAULT NULL, renewal VARCHAR(255) DEFAULT NULL, termination_clause_status TINYINT(1) DEFAULT NULL, termination_clause VARCHAR(255) DEFAULT NULL, electricity_fixed VARCHAR(255) DEFAULT NULL, other_utility_cost LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', frequency_of_lease_payments VARCHAR(255) DEFAULT NULL, annual_escalation_type VARCHAR(255) DEFAULT NULL, annual_escalation VARCHAR(255) DEFAULT NULL, annual_escalation_date VARCHAR(255) DEFAULT NULL, deposit_status TINYINT(1) DEFAULT NULL, deposit_amount VARCHAR(255) DEFAULT NULL, management_status TINYINT(1) DEFAULT NULL, created DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, INDEX IDX_E6C77495D48E7AED (landlord_id), INDEX IDX_E6C77495F6BD1646 (site_id), INDEX IDX_E6C77495EE68017B (electricity_type_id), INDEX IDX_E6C77495C48676C8 (deposit_type_id), INDEX IDX_E6C7749579CF281C (document_status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_document (id INT AUTO_INCREMENT NOT NULL, lease_id INT NOT NULL, type_id INT NOT NULL, document VARCHAR(255) NOT NULL, created DATETIME NOT NULL, INDEX IDX_97160E97D3CA542C (lease_id), INDEX IDX_97160E97C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rental_cost_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_rental_cost (id INT AUTO_INCREMENT NOT NULL, lease_id INT NOT NULL, category_id INT NOT NULL, start_date DATETIME NOT NULL, INDEX IDX_A01F4006D3CA542C (lease_id), INDEX IDX_A01F400612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_other_utility_cost_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_deposit_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_document_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_cpi (id INT AUTO_INCREMENT NOT NULL, lease_id INT NOT NULL, year VARCHAR(255) NOT NULL, percentage VARCHAR(255) NOT NULL, INDEX IDX_F75EC245D3CA542C (lease_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495D48E7AED FOREIGN KEY (landlord_id) REFERENCES landlord (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495EE68017B FOREIGN KEY (electricity_type_id) REFERENCES lease_electricity_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495C48676C8 FOREIGN KEY (deposit_type_id) REFERENCES lease_deposit_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C7749579CF281C FOREIGN KEY (document_status_id) REFERENCES lease_document_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lease_document ADD CONSTRAINT FK_97160E97D3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease_document ADD CONSTRAINT FK_97160E97C54C8C93 FOREIGN KEY (type_id) REFERENCES lease_document_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease_rental_cost ADD CONSTRAINT FK_A01F4006D3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease_rental_cost ADD CONSTRAINT FK_A01F400612469DE2 FOREIGN KEY (category_id) REFERENCES rental_cost_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease_cpi ADD CONSTRAINT FK_F75EC245D3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C77495EE68017B');
        $this->addSql('ALTER TABLE lease_document DROP FOREIGN KEY FK_97160E97D3CA542C');
        $this->addSql('ALTER TABLE lease_rental_cost DROP FOREIGN KEY FK_A01F4006D3CA542C');
        $this->addSql('ALTER TABLE lease_cpi DROP FOREIGN KEY FK_F75EC245D3CA542C');
        $this->addSql('ALTER TABLE lease_rental_cost DROP FOREIGN KEY FK_A01F400612469DE2');
        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C77495C48676C8');
        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C7749579CF281C');
        $this->addSql('ALTER TABLE lease_document DROP FOREIGN KEY FK_97160E97C54C8C93');
        $this->addSql('DROP TABLE lease_type');
        $this->addSql('DROP TABLE lease_electricity_type');
        $this->addSql('DROP TABLE lease');
        $this->addSql('DROP TABLE lease_document');
        $this->addSql('DROP TABLE rental_cost_category');
        $this->addSql('DROP TABLE lease_rental_cost');
        $this->addSql('DROP TABLE lease_other_utility_cost_category');
        $this->addSql('DROP TABLE lease_deposit_type');
        $this->addSql('DROP TABLE lease_document_type');
        $this->addSql('DROP TABLE lease_cpi');
    }
}
