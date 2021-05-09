<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210407184401 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE writing ADD user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE writing ADD CONSTRAINT FK_ED98FD9B9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_ED98FD9B9D86650F ON writing (user_id_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE writing DROP FOREIGN KEY FK_ED98FD9B9D86650F');
        $this->addSql('DROP INDEX IDX_ED98FD9B9D86650F ON writing');
        $this->addSql('ALTER TABLE writing DROP user_id_id');
    }
}
