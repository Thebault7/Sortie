<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200215200314 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE participants (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE participant ADD participants_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11838709D5 FOREIGN KEY (participants_id) REFERENCES participants (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B11838709D5 ON participant (participants_id)');
        $this->addSql('ALTER TABLE sortie ADD participants_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2838709D5 FOREIGN KEY (participants_id) REFERENCES participants (id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2838709D5 ON sortie (participants_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11838709D5');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2838709D5');
        $this->addSql('DROP TABLE participants');
        $this->addSql('DROP INDEX IDX_D79F6B11838709D5 ON participant');
        $this->addSql('ALTER TABLE participant DROP participants_id');
        $this->addSql('DROP INDEX IDX_3C3FD3F2838709D5 ON sortie');
        $this->addSql('ALTER TABLE sortie DROP participants_id');
    }
}
