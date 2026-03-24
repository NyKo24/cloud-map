<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CloudWatch Alarm table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cloud_watch_alarm (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, alarm_name VARCHAR(255) DEFAULT NULL, alarm_arn VARCHAR(2048) DEFAULT NULL, alarm_description LONGTEXT DEFAULT NULL, state_value VARCHAR(255) DEFAULT NULL, state_reason LONGTEXT DEFAULT NULL, metric_name VARCHAR(255) DEFAULT NULL, namespace VARCHAR(255) DEFAULT NULL, statistic VARCHAR(255) DEFAULT NULL, period INT DEFAULT NULL, evaluation_periods INT DEFAULT NULL, threshold DOUBLE PRECISION DEFAULT NULL, comparison_operator VARCHAR(255) DEFAULT NULL, actions_enabled TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_CW_ALARM_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cloud_watch_alarm ADD CONSTRAINT FK_CW_ALARM_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cloud_watch_alarm DROP FOREIGN KEY FK_CW_ALARM_CRAWL');
        $this->addSql('DROP TABLE cloud_watch_alarm');
    }
}
