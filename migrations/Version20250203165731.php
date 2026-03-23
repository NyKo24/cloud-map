<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203165731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aws_account (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, arn VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, aws_id VARCHAR(12) DEFAULT NULL, joined_method VARCHAR(7) DEFAULT NULL, joined_timestamp DATETIME DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, status VARCHAR(15) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4B3AF1559395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aws_account ADD CONSTRAINT FK_4B3AF1559395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aws_account DROP FOREIGN KEY FK_4B3AF1559395C3F3');
        $this->addSql('DROP TABLE aws_account');
    }
}
