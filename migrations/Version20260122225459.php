<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122225459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
    CREATE TABLE manager (
        id INT AUTO_INCREMENT NOT NULL,
        uuid CHAR(36) NOT NULL,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX UNIQ_FA2425B9A76ED395 (user_id),
        UNIQUE INDEX unique_uuid (uuid),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

// User table
        $this->addSql('
    CREATE TABLE user (
        id INT AUTO_INCREMENT NOT NULL,
        uuid CHAR(36) NOT NULL,
        email_address VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        status VARCHAR(255) NOT NULL,
        locale VARCHAR(4) DEFAULT \'de\' NOT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX unique_email_address (email_address),
        UNIQUE INDEX unique_uuid (uuid),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

// User verification table
        $this->addSql('
    CREATE TABLE user_verification (
        id INT AUTO_INCREMENT NOT NULL,
        user_id INT DEFAULT NULL,
        uuid CHAR(36) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX UNIQ_DA3DB909A76ED395 (user_id),
        UNIQUE INDEX unique_uuid (uuid),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

// Messenger messages table (already optimal)
        $this->addSql('
    CREATE TABLE messenger_messages (
        id BIGINT AUTO_INCREMENT NOT NULL,
        body LONGTEXT NOT NULL,
        headers LONGTEXT NOT NULL,
        queue_name VARCHAR(190) NOT NULL,
        created_at DATETIME NOT NULL,
        available_at DATETIME NOT NULL,
        delivered_at DATETIME DEFAULT NULL,
        INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750
            (queue_name, available_at, delivered_at, id),

        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4
');

// Foreign keys
        $this->addSql('
    ALTER TABLE manager
    ADD CONSTRAINT FK_FA2425B9A76ED395
    FOREIGN KEY (user_id)
    REFERENCES user (id)
');

        $this->addSql('
    ALTER TABLE user_verification
    ADD CONSTRAINT FK_DA3DB909A76ED395
    FOREIGN KEY (user_id)
    REFERENCES user (id)
');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manager DROP FOREIGN KEY FK_FA2425B9A76ED395');
        $this->addSql('ALTER TABLE user_verification DROP FOREIGN KEY FK_DA3DB909A76ED395');
        $this->addSql('DROP TABLE manager');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_verification');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
