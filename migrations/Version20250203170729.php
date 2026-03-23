<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203170729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crawl_version (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, version VARCHAR(255) NOT NULL, INDEX IDX_E5D2A7079395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crawl_version ADD CONSTRAINT FK_E5D2A7079395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE aws_account ADD crawl_id INT NOT NULL');
        $this->addSql('ALTER TABLE aws_account ADD CONSTRAINT FK_4B3AF155201B0D8A FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
        $this->addSql('CREATE INDEX IDX_4B3AF155201B0D8A ON aws_account (crawl_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aws_account DROP FOREIGN KEY FK_4B3AF155201B0D8A');
        $this->addSql('ALTER TABLE crawl_version DROP FOREIGN KEY FK_E5D2A7079395C3F3');
        $this->addSql('DROP TABLE crawl_version');
        $this->addSql('DROP INDEX IDX_4B3AF155201B0D8A ON aws_account');
        $this->addSql('ALTER TABLE aws_account DROP crawl_id');
    }
}
