<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250712211554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vpc_cidr_block_state (id INT AUTO_INCREMENT NOT NULL, state VARCHAR(255) DEFAULT NULL, status_message VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vpc_cidr_block_association ADD cidr_block_state_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vpc_cidr_block_association ADD CONSTRAINT FK_93DABB8D13C7DBA FOREIGN KEY (cidr_block_state_id) REFERENCES vpc_cidr_block_state (id)');
        $this->addSql('CREATE INDEX IDX_93DABB8D13C7DBA ON vpc_cidr_block_association (cidr_block_state_id)');
        $this->addSql('ALTER TABLE vpc_ipv6_cidr_block_association ADD ipv6_cidr_block_state_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vpc_ipv6_cidr_block_association ADD CONSTRAINT FK_317AFB5DEFED5862 FOREIGN KEY (ipv6_cidr_block_state_id) REFERENCES vpc_cidr_block_state (id)');
        $this->addSql('CREATE INDEX IDX_317AFB5DEFED5862 ON vpc_ipv6_cidr_block_association (ipv6_cidr_block_state_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vpc_cidr_block_association DROP FOREIGN KEY FK_93DABB8D13C7DBA');
        $this->addSql('ALTER TABLE vpc_ipv6_cidr_block_association DROP FOREIGN KEY FK_317AFB5DEFED5862');
        $this->addSql('DROP TABLE vpc_cidr_block_state');
        $this->addSql('DROP INDEX IDX_93DABB8D13C7DBA ON vpc_cidr_block_association');
        $this->addSql('ALTER TABLE vpc_cidr_block_association DROP cidr_block_state_id');
        $this->addSql('DROP INDEX IDX_317AFB5DEFED5862 ON vpc_ipv6_cidr_block_association');
        $this->addSql('ALTER TABLE vpc_ipv6_cidr_block_association DROP ipv6_cidr_block_state_id');
    }
}
