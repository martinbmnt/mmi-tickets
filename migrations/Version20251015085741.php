<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015085741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add confirmedAt field on Reservation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD COLUMN confirmed_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__reservation AS SELECT id, concert_id, created_at, status, pseudo FROM reservation');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('CREATE TABLE reservation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, concert_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , status VARCHAR(255) NOT NULL, pseudo VARCHAR(255) NOT NULL, CONSTRAINT FK_42C8495583C97B2E FOREIGN KEY (concert_id) REFERENCES concert (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO reservation (id, concert_id, created_at, status, pseudo) SELECT id, concert_id, created_at, status, pseudo FROM __temp__reservation');
        $this->addSql('DROP TABLE __temp__reservation');
        $this->addSql('CREATE INDEX IDX_42C8495583C97B2E ON reservation (concert_id)');
    }
}
