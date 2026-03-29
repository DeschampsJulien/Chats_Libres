<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327174147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fiche_chat (id INT AUTO_INCREMENT NOT NULL, donnees JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, lieu_id INT NOT NULL, UNIQUE INDEX UNIQ_8F2D65216AB213CC (lieu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lieu (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, nom VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, commentaire LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE fiche_chat ADD CONSTRAINT FK_8F2D65216AB213CC FOREIGN KEY (lieu_id) REFERENCES lieu (id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO lieu (type, nom, latitude, longitude, commentaire, created_at, updated_at) VALUES ('maison', 'Centre de Frontignan', 43.4484, 3.754, 'Point de repere principal sur Frontignan.', NOW(), NOW())");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_chat DROP FOREIGN KEY FK_8F2D65216AB213CC');
        $this->addSql('DROP TABLE fiche_chat');
        $this->addSql('DROP TABLE lieu');
    }
}
