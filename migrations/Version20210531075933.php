<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210531075933 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE libelle');
        $this->addSql('ALTER TABLE courrier CHANGE notes notes LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE upload ADD courier_id INT NOT NULL, ADD valide TINYINT(1) NOT NULL, ADD is_disabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61FE3D8151C FOREIGN KEY (courier_id) REFERENCES courrier (id)');
        $this->addSql('CREATE INDEX IDX_17BDE61FE3D8151C ON upload (courier_id)');
        $this->addSql('ALTER TABLE user ADD status TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE libelle (id INT AUTO_INCREMENT NOT NULL, nom_l VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, prenom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, numero INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE courrier CHANGE notes notes VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61FE3D8151C');
        $this->addSql('DROP INDEX IDX_17BDE61FE3D8151C ON upload');
        $this->addSql('ALTER TABLE upload DROP courier_id, DROP valide, DROP is_disabled');
        $this->addSql('ALTER TABLE user DROP status');
    }
}
