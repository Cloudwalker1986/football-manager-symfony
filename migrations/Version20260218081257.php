<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218081257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
    CREATE TABLE club (
        id INT AUTO_INCREMENT NOT NULL,
        uuid CHAR(36) NOT NULL,
        manager_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        short_name VARCHAR(10) NOT NULL,
        budget BIGINT NOT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX unique_uuid (uuid),
        UNIQUE INDEX UNIQ_B8EE3872783E3463 (manager_id),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

        $this->addSql('
    CREATE TABLE team (
        id INT AUTO_INCREMENT NOT NULL,
        uuid CHAR(36) NOT NULL,
        club_id INT NOT NULL,
        type VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX unique_uuid (uuid),
        INDEX IDX_C4E0A61F61190A32 (club_id),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

        $this->addSql('
    ALTER TABLE club
    ADD CONSTRAINT FK_B8EE3872783E3463
    FOREIGN KEY (manager_id) REFERENCES manager (id)
');

        $this->addSql('
    ALTER TABLE team
    ADD CONSTRAINT FK_C4E0A61F61190A32
    FOREIGN KEY (club_id) REFERENCES club (id)
');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE3872783E3463');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F61190A32');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE team');
    }
}
