<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190924085722 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE lease_cpi');
        $this->addSql('DROP TABLE lease_cpirate');
        $this->addSql('ALTER TABLE lease ADD annual_escalation_cpi VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lease_cpi (id INT AUTO_INCREMENT NOT NULL, lease_id INT NOT NULL, year VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, percentage VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_F75EC245D3CA542C (lease_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE lease_cpirate (id INT AUTO_INCREMENT NOT NULL, lease_id INT NOT NULL, year VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, value VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_E6ECC27DD3CA542C (lease_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE lease_cpi ADD CONSTRAINT FK_F75EC245D3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease_cpirate ADD CONSTRAINT FK_E6ECC27DD3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease DROP annual_escalation_cpi');
    }
}
