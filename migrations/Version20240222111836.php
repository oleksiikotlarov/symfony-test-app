<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222111836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE locale CHANGE iso_code iso_code VARCHAR(5) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4180C69862B6A45E ON locale (iso_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_4180C69862B6A45E ON locale');
        $this->addSql('ALTER TABLE locale CHANGE iso_code iso_code VARCHAR(3) NOT NULL');
    }
}
