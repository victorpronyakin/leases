<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190909102251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE landlord_contact (id INT AUTO_INCREMENT NOT NULL, landlord_id INT NOT NULL, type_id INT NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, mobile VARCHAR(255) DEFAULT NULL, landline VARCHAR(255) DEFAULT NULL, INDEX IDX_405EFF26D48E7AED (landlord_id), INDEX IDX_405EFF26C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE landlord_contact ADD CONSTRAINT FK_405EFF26D48E7AED FOREIGN KEY (landlord_id) REFERENCES landlord (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE landlord_contact ADD CONSTRAINT FK_405EFF26C54C8C93 FOREIGN KEY (type_id) REFERENCES landlord_contact_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE landlord_contact');
    }
}
