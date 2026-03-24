<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add RDS Instance table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rds_instance (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, db_instance_identifier VARCHAR(255) DEFAULT NULL, db_instance_class VARCHAR(255) DEFAULT NULL, engine VARCHAR(255) DEFAULT NULL, engine_version VARCHAR(255) DEFAULT NULL, db_instance_status VARCHAR(255) DEFAULT NULL, master_username VARCHAR(255) DEFAULT NULL, allocated_storage INT DEFAULT NULL, availability_zone VARCHAR(255) DEFAULT NULL, multi_az TINYINT(1) DEFAULT NULL, storage_type VARCHAR(255) DEFAULT NULL, storage_encrypted TINYINT(1) DEFAULT NULL, db_subnet_group_name VARCHAR(255) DEFAULT NULL, endpoint JSON DEFAULT NULL, publicly_accessible TINYINT(1) DEFAULT NULL, vpc_security_groups JSON DEFAULT NULL, tag_list JSON DEFAULT NULL, instance_create_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', db_instance_arn VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_RDSINSTANCE_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rds_instance ADD CONSTRAINT FK_RDSINSTANCE_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rds_instance DROP FOREIGN KEY FK_RDSINSTANCE_CRAWL');
        $this->addSql('DROP TABLE rds_instance');
    }
}
