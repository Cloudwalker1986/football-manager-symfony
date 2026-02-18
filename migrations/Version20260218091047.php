<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218091047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
    CREATE TABLE football_association (
        id INT AUTO_INCREMENT NOT NULL,
        uuid CHAR(36) NOT NULL,
        name VARCHAR(255) NOT NULL,
        code VARCHAR(10) DEFAULT NULL,
        country_code VARCHAR(2) DEFAULT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX unique_uuid (uuid),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

        $this->addSql('
    CREATE TABLE league (
        id INT AUTO_INCREMENT NOT NULL,
        uuid CHAR(36) NOT NULL,
        association_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        level INT NOT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX unique_uuid (uuid),
        INDEX idx_league_association_id (association_id),
        INDEX idx_league_association_level (association_id, level),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

        $this->addSql('
    ALTER TABLE league
    ADD CONSTRAINT FK_3EB4C318EFB9C8A5
    FOREIGN KEY (association_id) REFERENCES football_association (id)
');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE league DROP FOREIGN KEY FK_3EB4C318EFB9C8A5');
        $this->addSql('DROP TABLE football_association');
        $this->addSql('DROP TABLE league');
    }
}
