<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add EC2 Instance table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ec2_instance (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, instance_id VARCHAR(255) DEFAULT NULL, instance_type VARCHAR(255) DEFAULT NULL, state JSON DEFAULT NULL, platform_details VARCHAR(255) DEFAULT NULL, architecture VARCHAR(255) DEFAULT NULL, private_ip_address VARCHAR(45) DEFAULT NULL, public_ip_address VARCHAR(45) DEFAULT NULL, vpc_id VARCHAR(255) DEFAULT NULL, subnet_id VARCHAR(255) DEFAULT NULL, launch_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', image_id VARCHAR(255) DEFAULT NULL, key_name VARCHAR(255) DEFAULT NULL, placement JSON DEFAULT NULL, monitoring JSON DEFAULT NULL, security_groups JSON DEFAULT NULL, tags JSON DEFAULT NULL, private_dns_name VARCHAR(255) DEFAULT NULL, public_dns_name VARCHAR(255) DEFAULT NULL, ebs_optimized TINYINT(1) DEFAULT NULL, ena_support TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B7856EC0201B0D8A (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ec2_instance ADD CONSTRAINT FK_B7856EC0201B0D8A FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ec2_instance DROP FOREIGN KEY FK_B7856EC0201B0D8A');
        $this->addSql('DROP TABLE ec2_instance');
    }
}
