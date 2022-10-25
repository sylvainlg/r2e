<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190926204147 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_1BD0E1FA51A5BC03');
        $this->addSql('CREATE TEMPORARY TABLE __temp__log_event AS SELECT id, feed_id, type, trace, datetime FROM log_event');
        $this->addSql('DROP TABLE log_event');
        $this->addSql('CREATE TABLE log_event (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , feed_id INTEGER NOT NULL, type VARCHAR(255) DEFAULT NULL COLLATE BINARY, trace CLOB DEFAULT NULL COLLATE BINARY, datetime DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_1BD0E1FA51A5BC03 FOREIGN KEY (feed_id) REFERENCES rss_feed (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO log_event (id, feed_id, type, trace, datetime) SELECT id, feed_id, type, trace, datetime FROM __temp__log_event');
        $this->addSql('DROP TABLE __temp__log_event');
        $this->addSql('CREATE INDEX IDX_1BD0E1FA51A5BC03 ON log_event (feed_id)');
        $this->addSql('DROP INDEX IDX_63263FF0A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, user_id, url, last_update FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, url VARCHAR(2000) NOT NULL COLLATE BINARY, last_update DATETIME DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, status VARCHAR(10) DEFAULT \'OK\' NOT NULL, CONSTRAINT FK_63263FF0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO rss_feed (id, user_id, url, last_update) SELECT id, user_id, url, last_update FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
        $this->addSql('CREATE INDEX IDX_63263FF0A76ED395 ON rss_feed (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_1BD0E1FA51A5BC03');
        $this->addSql('CREATE TEMPORARY TABLE __temp__log_event AS SELECT id, feed_id, datetime, type, trace FROM log_event');
        $this->addSql('DROP TABLE log_event');
        $this->addSql('CREATE TABLE log_event (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , feed_id INTEGER NOT NULL, datetime DATETIME NOT NULL, type VARCHAR(255) DEFAULT NULL, trace CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO log_event (id, feed_id, datetime, type, trace) SELECT id, feed_id, datetime, type, trace FROM __temp__log_event');
        $this->addSql('DROP TABLE __temp__log_event');
        $this->addSql('CREATE INDEX IDX_1BD0E1FA51A5BC03 ON log_event (feed_id)');
        $this->addSql('DROP INDEX IDX_63263FF0A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, user_id, url, last_update FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, url VARCHAR(2000) NOT NULL, last_update DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO rss_feed (id, user_id, url, last_update) SELECT id, user_id, url, last_update FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
        $this->addSql('CREATE INDEX IDX_63263FF0A76ED395 ON rss_feed (user_id)');
    }
}
