<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191125090620 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE actual_reminder ADD issue_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE actual_reminder ADD CONSTRAINT FK_C87164605E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C87164605E7AA58C ON actual_reminder (issue_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE actual_reminder DROP FOREIGN KEY FK_C87164605E7AA58C');
        $this->addSql('DROP INDEX IDX_C87164605E7AA58C ON actual_reminder');
        $this->addSql('ALTER TABLE actual_reminder DROP issue_id');
    }
}
