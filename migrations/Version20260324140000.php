<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Security Group and Security Group Rule tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE security_group (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, group_id VARCHAR(255) DEFAULT NULL, group_name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, vpc_id VARCHAR(255) DEFAULT NULL, owner_id VARCHAR(255) DEFAULT NULL, tags JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_SECURITYGROUP_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE security_group_rule (id INT AUTO_INCREMENT NOT NULL, security_group_id INT NOT NULL, ip_protocol VARCHAR(255) DEFAULT NULL, from_port INT DEFAULT NULL, to_port INT DEFAULT NULL, cidr_ip VARCHAR(255) DEFAULT NULL, cidr_ipv6 VARCHAR(255) DEFAULT NULL, direction VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_SECURITYGROUPRULE_SG (security_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE security_group ADD CONSTRAINT FK_SECURITYGROUP_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
        $this->addSql('ALTER TABLE security_group_rule ADD CONSTRAINT FK_SECURITYGROUPRULE_SG FOREIGN KEY (security_group_id) REFERENCES security_group (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE security_group_rule DROP FOREIGN KEY FK_SECURITYGROUPRULE_SG');
        $this->addSql('ALTER TABLE security_group DROP FOREIGN KEY FK_SECURITYGROUP_CRAWL');
        $this->addSql('DROP TABLE security_group_rule');
        $this->addSql('DROP TABLE security_group');
    }
}
