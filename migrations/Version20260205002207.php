<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205002207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manager_history (
            id INT AUTO_INCREMENT NOT NULL,
            uuid CHAR(36) NOT NULL,
            manager_id INT DEFAULT NULL,
            message LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT NULL,
            updated_at DATETIME DEFAULT NULL,
            INDEX IDX_9977455D783E3463 (manager_id),
            UNIQUE INDEX unique_uuid (uuid),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE manager_history
            ADD CONSTRAINT FK_9977455D783E3463
            FOREIGN KEY (manager_id) REFERENCES manager (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manager_history
            DROP FOREIGN KEY FK_9977455D783E3463');
        $this->addSql('DROP TABLE manager_history');
    }
}
