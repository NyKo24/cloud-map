<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ECS Cluster and EKS Cluster tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ecs_cluster (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, cluster_arn VARCHAR(2048) DEFAULT NULL, cluster_name VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, registered_container_instances_count INT DEFAULT NULL, running_tasks_count INT DEFAULT NULL, active_services_count INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_ECS_CLUSTER_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ecs_cluster ADD CONSTRAINT FK_ECS_CLUSTER_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');

        $this->addSql('CREATE TABLE eks_cluster (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, arn VARCHAR(2048) DEFAULT NULL, version VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, platform_version VARCHAR(255) DEFAULT NULL, endpoint LONGTEXT DEFAULT NULL, role_arn VARCHAR(2048) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_EKS_CLUSTER_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE eks_cluster ADD CONSTRAINT FK_EKS_CLUSTER_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ecs_cluster DROP FOREIGN KEY FK_ECS_CLUSTER_CRAWL');
        $this->addSql('DROP TABLE ecs_cluster');

        $this->addSql('ALTER TABLE eks_cluster DROP FOREIGN KEY FK_EKS_CLUSTER_CRAWL');
        $this->addSql('DROP TABLE eks_cluster');
    }
}
