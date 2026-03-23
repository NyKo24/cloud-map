<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714135625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer CHANGE aws_root_role_arn aws_root_role_arn VARCHAR(255) DEFAULT NULL, CHANGE aws_root_organisation_id aws_root_organisation_id VARCHAR(255) DEFAULT NULL, CHANGE aws_account_role_name aws_account_role_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer CHANGE aws_root_role_arn aws_root_role_arn VARCHAR(255) NOT NULL, CHANGE aws_root_organisation_id aws_root_organisation_id VARCHAR(255) NOT NULL, CHANGE aws_account_role_name aws_account_role_name VARCHAR(255) NOT NULL');
    }
}
