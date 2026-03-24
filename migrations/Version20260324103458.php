<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324103458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add S3 Bucket entity (RMP-3)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE s3_bucket (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, creation_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', region VARCHAR(255) DEFAULT NULL, encryption_enabled TINYINT(1) DEFAULT NULL, versioning_status VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_A705B17B201B0D8A (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE s3_bucket ADD CONSTRAINT FK_A705B17B201B0D8A FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE s3_bucket DROP FOREIGN KEY FK_A705B17B201B0D8A');
        $this->addSql('DROP TABLE s3_bucket');
    }
}
