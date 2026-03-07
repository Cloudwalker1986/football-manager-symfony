<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260307000402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stadium (name VARCHAR(255) NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, club_id INT NOT NULL, UNIQUE INDEX unique_uuid (uuid), UNIQUE INDEX unique_club_id (club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stadium_block (name VARCHAR(255) NOT NULL, stand_seat_capacity INT NOT NULL, stand_seat_price_category INT NOT NULL, stand_seat_reserved_space INT NOT NULL, sit_seat_capacity INT NOT NULL, sit_seat_price_category INT NOT NULL, sit_seat_reserved_space INT NOT NULL, vip_loge_capacity INT NOT NULL, vip_loge_price_category INT NOT NULL, vip_loge_reserved_space INT NOT NULL, id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, stadium_id INT NOT NULL, INDEX IDX_20A05E827E860E36 (stadium_id), UNIQUE INDEX unique_uuid (uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stadium_environment (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, club_id INT NOT NULL, UNIQUE INDEX unique_uuid (uuid), UNIQUE INDEX unique_club_id (club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE stadium ADD CONSTRAINT FK_E604044F61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE stadium_block ADD CONSTRAINT FK_20A05E827E860E36 FOREIGN KEY (stadium_id) REFERENCES stadium (id)');
        $this->addSql('ALTER TABLE stadium_environment ADD CONSTRAINT FK_AA726FC461190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadium DROP FOREIGN KEY FK_E604044F61190A32');
        $this->addSql('ALTER TABLE stadium_block DROP FOREIGN KEY FK_20A05E827E860E36');
        $this->addSql('ALTER TABLE stadium_environment DROP FOREIGN KEY FK_AA726FC461190A32');
        $this->addSql('DROP TABLE stadium');
        $this->addSql('DROP TABLE stadium_block');
        $this->addSql('DROP TABLE stadium_environment');
    }
}
