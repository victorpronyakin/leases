<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190912085809 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE site_inventory (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, category_id INT NOT NULL, quantity INT NOT NULL, info LONGTEXT DEFAULT NULL, INDEX IDX_D7273C43F6BD1646 (site_id), INDEX IDX_D7273C4312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_inventory_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emergency_contact (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, contact_id INT NOT NULL, INDEX IDX_FE1C6190F6BD1646 (site_id), INDEX IDX_FE1C6190E7A1254A (contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hours_of_access (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, hours_of_access_id INT DEFAULT NULL, number VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, street_number VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, map_link VARCHAR(255) DEFAULT NULL, erf VARCHAR(255) NOT NULL, other_access LONGTEXT DEFAULT NULL, created DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, INDEX IDX_694309E492ACBD84 (hours_of_access_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE site_inventory ADD CONSTRAINT FK_D7273C43F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site_inventory ADD CONSTRAINT FK_D7273C4312469DE2 FOREIGN KEY (category_id) REFERENCES site_inventory_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emergency_contact ADD CONSTRAINT FK_FE1C6190F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emergency_contact ADD CONSTRAINT FK_FE1C6190E7A1254A FOREIGN KEY (contact_id) REFERENCES landlord_contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E492ACBD84 FOREIGN KEY (hours_of_access_id) REFERENCES hours_of_access (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site_inventory DROP FOREIGN KEY FK_D7273C4312469DE2');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E492ACBD84');
        $this->addSql('ALTER TABLE site_inventory DROP FOREIGN KEY FK_D7273C43F6BD1646');
        $this->addSql('ALTER TABLE emergency_contact DROP FOREIGN KEY FK_FE1C6190F6BD1646');
        $this->addSql('DROP TABLE site_inventory');
        $this->addSql('DROP TABLE site_inventory_category');
        $this->addSql('DROP TABLE emergency_contact');
        $this->addSql('DROP TABLE hours_of_access');
        $this->addSql('DROP TABLE site');
    }
}
