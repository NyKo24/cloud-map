<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212232917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE block_public_access_states (id INT AUTO_INCREMENT NOT NULL, internet_gateway_block_mode VARCHAR(19) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, vpc_id INT DEFAULT NULL, key_name VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, INDEX IDX_389B78325F8EB7F (vpc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vpc (id INT AUTO_INCREMENT NOT NULL, block_public_access_states_id INT DEFAULT NULL, crawl_id INT NOT NULL, cidr_block VARCHAR(255) DEFAULT NULL, dhcp_options_id VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, vpc_id VARCHAR(255) DEFAULT NULL, owner_id VARCHAR(255) DEFAULT NULL, instance_tenancy VARCHAR(255) DEFAULT NULL, is_default TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_54B984E47D95AB0A (block_public_access_states_id), INDEX IDX_54B984E4201B0D8A (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vpc_cidr_block_association (id INT AUTO_INCREMENT NOT NULL, vpc_id INT DEFAULT NULL, association_id VARCHAR(255) DEFAULT NULL, cidr_block VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_93DABB8D25F8EB7F (vpc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vpc_ipv6_cidr_block_association (id INT AUTO_INCREMENT NOT NULL, vpc_id INT DEFAULT NULL, association_id VARCHAR(255) DEFAULT NULL, ipv6_cidr_block VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_317AFB5D25F8EB7F (vpc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B78325F8EB7F FOREIGN KEY (vpc_id) REFERENCES vpc (id)');
        $this->addSql('ALTER TABLE vpc ADD CONSTRAINT FK_54B984E47D95AB0A FOREIGN KEY (block_public_access_states_id) REFERENCES block_public_access_states (id)');
        $this->addSql('ALTER TABLE vpc ADD CONSTRAINT FK_54B984E4201B0D8A FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
        $this->addSql('ALTER TABLE vpc_cidr_block_association ADD CONSTRAINT FK_93DABB8D25F8EB7F FOREIGN KEY (vpc_id) REFERENCES vpc (id)');
        $this->addSql('ALTER TABLE vpc_ipv6_cidr_block_association ADD CONSTRAINT FK_317AFB5D25F8EB7F FOREIGN KEY (vpc_id) REFERENCES vpc (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B78325F8EB7F');
        $this->addSql('ALTER TABLE vpc DROP FOREIGN KEY FK_54B984E47D95AB0A');
        $this->addSql('ALTER TABLE vpc DROP FOREIGN KEY FK_54B984E4201B0D8A');
        $this->addSql('ALTER TABLE vpc_cidr_block_association DROP FOREIGN KEY FK_93DABB8D25F8EB7F');
        $this->addSql('ALTER TABLE vpc_ipv6_cidr_block_association DROP FOREIGN KEY FK_317AFB5D25F8EB7F');
        $this->addSql('DROP TABLE block_public_access_states');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE vpc');
        $this->addSql('DROP TABLE vpc_cidr_block_association');
        $this->addSql('DROP TABLE vpc_ipv6_cidr_block_association');
    }
}
