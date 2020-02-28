<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191001092502 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE landlord_comments (id INT AUTO_INCREMENT NOT NULL, landlord_id INT NOT NULL, comment LONGTEXT NOT NULL, updated DATETIME NOT NULL, INDEX IDX_A59D9750D48E7AED (landlord_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE landlord_comments ADD CONSTRAINT FK_A59D9750D48E7AED FOREIGN KEY (landlord_id) REFERENCES landlord (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE landlord DROP comments');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE landlord_comments');
        $this->addSql('ALTER TABLE landlord ADD comments LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
