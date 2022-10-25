<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190907204001 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, url, user, last_update FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, url VARCHAR(2000) NOT NULL COLLATE BINARY, last_update DATETIME DEFAULT NULL, CONSTRAINT FK_63263FF0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO rss_feed (id, url, user_id, last_update) SELECT id, url, user, last_update FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
        $this->addSql('CREATE INDEX IDX_63263FF0A76ED395 ON rss_feed (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_63263FF0A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, url, last_update FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url VARCHAR(2000) NOT NULL, last_update DATETIME DEFAULT NULL, user VARCHAR(100) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO rss_feed (id, url, last_update) SELECT id, url, last_update FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
    }
}
