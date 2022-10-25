<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191004205904 extends AbstractMigration
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
        $this->addSql('CREATE TEMPORARY TABLE __temp__log_event AS SELECT id, feed_id, type, trace, datetime, message FROM log_event');
        $this->addSql('DROP TABLE log_event');
        $this->addSql('CREATE TABLE log_event (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , feed_id INTEGER NOT NULL, type VARCHAR(255) DEFAULT NULL COLLATE BINARY, trace CLOB DEFAULT NULL COLLATE BINARY, datetime DATETIME NOT NULL, message VARCHAR(255) DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_1BD0E1FA51A5BC03 FOREIGN KEY (feed_id) REFERENCES rss_feed (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO log_event (id, feed_id, type, trace, datetime, message) SELECT id, feed_id, type, trace, datetime, message FROM __temp__log_event');
        $this->addSql('DROP TABLE __temp__log_event');
        $this->addSql('CREATE INDEX IDX_1BD0E1FA51A5BC03 ON log_event (feed_id)');
        $this->addSql('DROP INDEX IDX_63263FF0A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, user_id, url, last_update, title, status FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, url VARCHAR(2000) NOT NULL COLLATE BINARY, last_update DATETIME DEFAULT NULL, title VARCHAR(255) DEFAULT NULL COLLATE BINARY, status VARCHAR(10) DEFAULT \'OK\' NOT NULL COLLATE BINARY, enabled BOOLEAN DEFAULT 1, CONSTRAINT FK_63263FF0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO rss_feed (id, user_id, url, last_update, title, status) SELECT id, user_id, url, last_update, title, status FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
        $this->addSql('CREATE INDEX IDX_63263FF0A76ED395 ON rss_feed (user_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, group_error_mail, send_email_on_error FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL COLLATE BINARY, roles CLOB NOT NULL COLLATE BINARY --(DC2Type:json)
        , password VARCHAR(255) NOT NULL COLLATE BINARY, group_error_mail BOOLEAN NOT NULL, send_email_on_error BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, group_error_mail, send_email_on_error) SELECT id, email, roles, password, group_error_mail, send_email_on_error FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_1BD0E1FA51A5BC03');
        $this->addSql('CREATE TEMPORARY TABLE __temp__log_event AS SELECT id, feed_id, datetime, type, trace, message FROM log_event');
        $this->addSql('DROP TABLE log_event');
        $this->addSql('CREATE TABLE log_event (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , feed_id INTEGER NOT NULL, datetime DATETIME NOT NULL, type VARCHAR(255) DEFAULT NULL, trace CLOB DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO log_event (id, feed_id, datetime, type, trace, message) SELECT id, feed_id, datetime, type, trace, message FROM __temp__log_event');
        $this->addSql('DROP TABLE __temp__log_event');
        $this->addSql('CREATE INDEX IDX_1BD0E1FA51A5BC03 ON log_event (feed_id)');
        $this->addSql('DROP INDEX IDX_63263FF0A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rss_feed AS SELECT id, user_id, url, last_update, title, status FROM rss_feed');
        $this->addSql('DROP TABLE rss_feed');
        $this->addSql('CREATE TABLE rss_feed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, url VARCHAR(2000) NOT NULL, last_update DATETIME DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, status VARCHAR(10) DEFAULT \'OK\' NOT NULL)');
        $this->addSql('INSERT INTO rss_feed (id, user_id, url, last_update, title, status) SELECT id, user_id, url, last_update, title, status FROM __temp__rss_feed');
        $this->addSql('DROP TABLE __temp__rss_feed');
        $this->addSql('CREATE INDEX IDX_63263FF0A76ED395 ON rss_feed (user_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, group_error_mail, send_email_on_error FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, group_error_mail BOOLEAN DEFAULT \'0\' NOT NULL, send_email_on_error BOOLEAN DEFAULT \'1\' NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, group_error_mail, send_email_on_error) SELECT id, email, roles, password, group_error_mail, send_email_on_error FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
