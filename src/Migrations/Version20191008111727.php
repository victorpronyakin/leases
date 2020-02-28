<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191008111727 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE user_permission ADD viewable TINYINT(1) DEFAULT NULL, ADD added TINYINT(1) DEFAULT NULL, ADD editable TINYINT(1) DEFAULT NULL, ADD removable TINYINT(1) DEFAULT NULL, DROP view, DROP edit, DROP remove');
        $this->addSql('TRUNCATE TABLE user_permission');
        $this->addSql('ALTER TABLE permission ADD viewable TINYINT(1) DEFAULT \'0\' NOT NULL, ADD added TINYINT(1) DEFAULT \'0\' NOT NULL, ADD editable TINYINT(1) DEFAULT \'0\' NOT NULL, ADD removable TINYINT(1) DEFAULT \'0\' NOT NULL, DROP path_view, DROP path_edit, DROP path_remove');
        $this->addSql('TRUNCATE TABLE permission');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE permission ADD path_view VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD path_edit VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD path_remove VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP viewable, DROP added, DROP editable, DROP removable');
        $this->addSql('ALTER TABLE user_permission ADD view TINYINT(1) DEFAULT NULL, ADD edit TINYINT(1) DEFAULT NULL, ADD remove TINYINT(1) DEFAULT NULL, DROP viewable, DROP added, DROP editable, DROP removable');
    }
}
