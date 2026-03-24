<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add IAM Role and IAM User tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE iam_role (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, role_name VARCHAR(255) DEFAULT NULL, role_id VARCHAR(255) DEFAULT NULL, arn VARCHAR(2048) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, create_date DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, max_session_duration INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_IAMROLE_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE iam_user (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, user_name VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, arn VARCHAR(2048) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, create_date DATETIME DEFAULT NULL, password_last_used DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_IAMUSER_CRAWL (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE iam_role ADD CONSTRAINT FK_IAMROLE_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
        $this->addSql('ALTER TABLE iam_user ADD CONSTRAINT FK_IAMUSER_CRAWL FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE iam_role DROP FOREIGN KEY FK_IAMROLE_CRAWL');
        $this->addSql('ALTER TABLE iam_user DROP FOREIGN KEY FK_IAMUSER_CRAWL');
        $this->addSql('DROP TABLE iam_role');
        $this->addSql('DROP TABLE iam_user');
    }
}
