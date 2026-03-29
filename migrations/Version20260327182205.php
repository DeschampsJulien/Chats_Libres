<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327182205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lieu_maison (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, commentaire LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('INSERT INTO lieu_maison (nom, latitude, longitude, commentaire, created_at, updated_at) SELECT nom, latitude, longitude, commentaire, created_at, updated_at FROM lieu WHERE type = "maison"');
        $this->addSql('ALTER TABLE fiche_chat ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, ADD commentaire LONGTEXT DEFAULT NULL');
        $this->addSql('UPDATE fiche_chat fc INNER JOIN lieu l ON fc.lieu_id = l.id SET fc.latitude = l.latitude, fc.longitude = l.longitude, fc.commentaire = l.commentaire WHERE l.type = "chat"');
        $this->addSql('UPDATE fiche_chat SET latitude = 0, longitude = 0 WHERE latitude IS NULL OR longitude IS NULL');
        $this->addSql('ALTER TABLE fiche_chat MODIFY latitude DOUBLE PRECISION NOT NULL, MODIFY longitude DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE fiche_chat DROP FOREIGN KEY `FK_8F2D65216AB213CC`');
        $this->addSql('DROP INDEX UNIQ_8F2D65216AB213CC ON fiche_chat');
        $this->addSql('ALTER TABLE fiche_chat DROP lieu_id');
        $this->addSql('DROP TABLE lieu');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lieu (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, commentaire LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('INSERT INTO lieu (type, nom, latitude, longitude, commentaire, created_at, updated_at) SELECT "maison", nom, latitude, longitude, commentaire, created_at, updated_at FROM lieu_maison');
        $this->addSql('INSERT INTO lieu (type, nom, latitude, longitude, commentaire, created_at, updated_at) SELECT "chat", COALESCE(NULLIF(nom_attribue, ""), CONCAT("Dossier ", COALESCE(NULLIF(dossier_numero, ""), id))), latitude, longitude, commentaire, created_at, updated_at FROM fiche_chat');
        $this->addSql('ALTER TABLE fiche_chat ADD lieu_id INT DEFAULT NULL');
        $this->addSql('UPDATE fiche_chat fc INNER JOIN lieu l ON l.type = "chat" AND l.latitude = fc.latitude AND l.longitude = fc.longitude AND ((l.commentaire IS NULL AND fc.commentaire IS NULL) OR l.commentaire = fc.commentaire) SET fc.lieu_id = l.id WHERE fc.lieu_id IS NULL');
        $this->addSql('UPDATE fiche_chat SET lieu_id = (SELECT id FROM lieu WHERE type = "chat" ORDER BY id DESC LIMIT 1) WHERE lieu_id IS NULL');
        $this->addSql('ALTER TABLE fiche_chat MODIFY lieu_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F2D65216AB213CC ON fiche_chat (lieu_id)');
        $this->addSql('ALTER TABLE fiche_chat ADD CONSTRAINT `FK_8F2D65216AB213CC` FOREIGN KEY (lieu_id) REFERENCES lieu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fiche_chat DROP latitude, DROP longitude, DROP commentaire');
        $this->addSql('DROP TABLE lieu_maison');
    }
}
