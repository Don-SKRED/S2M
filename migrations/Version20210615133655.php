<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210615133655 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pieces (id INT AUTO_INCREMENT NOT NULL, courrier_id INT NOT NULL, n_cmd BIGINT NOT NULL, n_recept BIGINT NOT NULL, n_bl BIGINT NOT NULL, fournisseur VARCHAR(255) NOT NULL, rayon VARCHAR(255) NOT NULL, d_reception VARCHAR(255) NOT NULL, montant_ht BIGINT NOT NULL, valide VARCHAR(255) NOT NULL, is_disabled TINYINT(1) NOT NULL, second_valide TINYINT(1) NOT NULL, valide_recipient TINYINT(1) NOT NULL, INDEX IDX_B92D74728BF41DC7 (courrier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE po (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pieces ADD CONSTRAINT FK_B92D74728BF41DC7 FOREIGN KEY (courrier_id) REFERENCES courrier (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pieces');
        $this->addSql('DROP TABLE po');
    }
}
