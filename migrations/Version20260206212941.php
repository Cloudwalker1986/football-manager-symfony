<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206212941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new table for messages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
    CREATE TABLE message (
        id INT AUTO_INCREMENT NOT NULL,
        uuid CHAR(36) NOT NULL,
        manager_id INT NOT NULL,
        sender_id INT DEFAULT NULL,
        state VARCHAR(255) DEFAULT \'unread\' NOT NULL,
        message LONGBLOB NOT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX unique_uuid (uuid),
        INDEX IDX_B6BD307F783E3463 (manager_id),
        INDEX IDX_B6BD307FF624B39D (sender_id),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

        $this->addSql('
    ALTER TABLE message
        ADD CONSTRAINT FK_B6BD307F783E3463 FOREIGN KEY (manager_id) REFERENCES manager (id)
');

        $this->addSql('
    ALTER TABLE message
        ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES manager (id)
');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F783E3463');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('DROP TABLE message');
    }
}
