<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190912201620 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE log_event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, feed_id INTEGER NOT NULL, datetime DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , type VARCHAR(255) DEFAULT NULL, trace CLOB DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_1BD0E1FA51A5BC03 ON log_event (feed_id)');
        $this->addSql('CREATE TABLE refresh_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('DROP INDEX IDX_63263FF0A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, user_id, url, last_update FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, url VARCHAR(2000) NOT NULL COLLATE BINARY, last_update DATETIME DEFAULT NULL, CONSTRAINT FK_63263FF0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO rss_feed (id, user_id, url, last_update) SELECT id, user_id, url, last_update FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
        $this->addSql('CREATE INDEX IDX_63263FF0A76ED395 ON rss_feed (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE log_event');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP INDEX IDX_63263FF0A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, user_id, url, last_update FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, url VARCHAR(2000) NOT NULL, last_update DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO rss_feed (id, user_id, url, last_update) SELECT id, user_id, url, last_update FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
        $this->addSql('CREATE INDEX IDX_63263FF0A76ED395 ON rss_feed (user_id)');
    }
}
