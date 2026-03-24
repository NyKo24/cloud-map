<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Route53 Hosted Zone table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE hosted_zone (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, hosted_zone_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, caller_reference VARCHAR(255) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, private_zone TINYINT(1) DEFAULT NULL, resource_record_set_count INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_HOSTEDZONE_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hosted_zone ADD CONSTRAINT FK_HOSTEDZONE_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hosted_zone DROP FOREIGN KEY FK_HOSTEDZONE_CRAWL');
        $this->addSql('DROP TABLE hosted_zone');
    }
}
