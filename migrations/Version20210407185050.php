<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210407185050 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE writing DROP FOREIGN KEY FK_ED98FD9BA76ED395');
        $this->addSql('DROP INDEX IDX_ED98FD9BA76ED395 ON writing');
        $this->addSql('ALTER TABLE writing ADD user INT NOT NULL, DROP user_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE writing ADD user_id INT DEFAULT NULL, DROP user');
        $this->addSql('ALTER TABLE writing ADD CONSTRAINT FK_ED98FD9BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_ED98FD9BA76ED395 ON writing (user_id)');
    }
}
