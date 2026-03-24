<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Load Balancer table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE load_balancer (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, load_balancer_arn VARCHAR(255) DEFAULT NULL, load_balancer_name VARCHAR(255) DEFAULT NULL, dns_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, scheme VARCHAR(255) DEFAULT NULL, vpc_id VARCHAR(255) DEFAULT NULL, state JSON DEFAULT NULL, created_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', availability_zones JSON DEFAULT NULL, ip_address_type VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_LOADBALANCER_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE load_balancer ADD CONSTRAINT FK_LOADBALANCER_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE load_balancer DROP FOREIGN KEY FK_LOADBALANCER_CRAWL');
        $this->addSql('DROP TABLE load_balancer');
    }
}
