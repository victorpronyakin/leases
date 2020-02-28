<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190927114058 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease ADD allocated_id INT DEFAULT NULL, ADD fee INT DEFAULT NULL, ADD fee_value VARCHAR(255) DEFAULT NULL, ADD fee_duration VARCHAR(255) DEFAULT NULL, ADD fee_escalation INT DEFAULT NULL, ADD proposed_lease VARCHAR(255) DEFAULT NULL, ADD target_renewal_rental VARCHAR(255) DEFAULT NULL, ADD target_renewal_escalation VARCHAR(255) DEFAULT NULL, ADD saving_amount VARCHAR(255) DEFAULT NULL, ADD saving_percentage VARCHAR(255) DEFAULT NULL, ADD billing_value VARCHAR(255) DEFAULT NULL, ADD escalation_saving VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495456D63E9 FOREIGN KEY (allocated_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E6C77495456D63E9 ON lease (allocated_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C77495456D63E9');
        $this->addSql('DROP INDEX IDX_E6C77495456D63E9 ON lease');
        $this->addSql('ALTER TABLE lease DROP allocated_id, DROP fee, DROP fee_value, DROP fee_duration, DROP fee_escalation, DROP proposed_lease, DROP target_renewal_rental, DROP target_renewal_escalation, DROP saving_amount, DROP saving_percentage, DROP billing_value, DROP escalation_saving');
    }
}
