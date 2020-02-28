<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191022082230 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE actual_reminder (id INT AUTO_INCREMENT NOT NULL, reminder_id INT NOT NULL, site_id INT DEFAULT NULL, lease_id INT DEFAULT NULL, status TINYINT(1) DEFAULT NULL, snooze_date DATETIME DEFAULT NULL, created DATETIME DEFAULT NULL, INDEX IDX_C8716460D987BE75 (reminder_id), INDEX IDX_C8716460F6BD1646 (site_id), INDEX IDX_C8716460D3CA542C (lease_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actual_reminder ADD CONSTRAINT FK_C8716460D987BE75 FOREIGN KEY (reminder_id) REFERENCES reminder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actual_reminder ADD CONSTRAINT FK_C8716460F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actual_reminder ADD CONSTRAINT FK_C8716460D3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE actual_reminder');
    }
}
