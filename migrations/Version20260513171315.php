<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260513171315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, name_normalized VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094F5E237E06 ON company (name)');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rating INTEGER NOT NULL, review_text CLOB NOT NULL, author_email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, company_id INTEGER NOT NULL, CONSTRAINT FK_794381C6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_794381C6979B1AD6 ON review (company_id)');
        $this->addSql('CREATE TABLE review_reaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(10) NOT NULL, session_id VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL, review_id INTEGER NOT NULL, CONSTRAINT FK_87958EFC3E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_87958EFC3E2E969B ON review_reaction (review_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_reaction_session ON review_reaction (review_id, session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE review_reaction');
    }
}
