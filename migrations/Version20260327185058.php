<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327185058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiche_maison ADD adresse VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE fiche_maison SET adresse = commentaire WHERE adresse IS NULL AND commentaire IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiche_maison DROP adresse');
    }
}
