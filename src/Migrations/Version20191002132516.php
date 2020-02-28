<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191002132516 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease DROP saving_amount, DROP saving_percentage, DROP billing_value, DROP escalation_saving');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease ADD saving_amount VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD saving_percentage VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD billing_value VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD escalation_saving VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
