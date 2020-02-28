<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190918135029 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE financial (id INT AUTO_INCREMENT NOT NULL, lease_id INT NOT NULL, lease_payment_status_id INT DEFAULT NULL, electricity_payment_status_id INT DEFAULT NULL, other_cost_payment_status_id INT DEFAULT NULL, month VARCHAR(255) NOT NULL, lease_expense VARCHAR(255) DEFAULT NULL, lease_charge VARCHAR(255) DEFAULT NULL, electricity_cost VARCHAR(255) DEFAULT NULL, other_cost VARCHAR(255) DEFAULT NULL, total VARCHAR(255) DEFAULT NULL, invoices LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_6E80AAACD3CA542C (lease_id), INDEX IDX_6E80AAAC36CC2966 (lease_payment_status_id), INDEX IDX_6E80AAAC2B7784C2 (electricity_payment_status_id), INDEX IDX_6E80AAAC92A0A4B7 (other_cost_payment_status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE financial ADD CONSTRAINT FK_6E80AAACD3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE financial ADD CONSTRAINT FK_6E80AAAC36CC2966 FOREIGN KEY (lease_payment_status_id) REFERENCES financial_status (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE financial ADD CONSTRAINT FK_6E80AAAC2B7784C2 FOREIGN KEY (electricity_payment_status_id) REFERENCES financial_status (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE financial ADD CONSTRAINT FK_6E80AAAC92A0A4B7 FOREIGN KEY (other_cost_payment_status_id) REFERENCES financial_status (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE financial');
    }
}
