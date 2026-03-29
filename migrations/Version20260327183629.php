<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327183629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE lieu_maison TO fiche_maison');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('RENAME TABLE fiche_maison TO lieu_maison');
    }
}
