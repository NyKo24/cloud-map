<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250817225021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lambda_environment (id INT AUTO_INCREMENT NOT NULL, lambda_function_id INT NOT NULL, variables JSON DEFAULT NULL, error JSON DEFAULT NULL, UNIQUE INDEX UNIQ_978370ACB6A1268F (lambda_function_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lambda_function (id INT AUTO_INCREMENT NOT NULL, crawl_id INT NOT NULL, function_name VARCHAR(255) DEFAULT NULL, function_arn VARCHAR(255) DEFAULT NULL, runtime VARCHAR(255) DEFAULT NULL, role VARCHAR(255) DEFAULT NULL, handler VARCHAR(255) DEFAULT NULL, code_size BIGINT DEFAULT NULL, description LONGTEXT DEFAULT NULL, timeout INT DEFAULT NULL, memory_size INT DEFAULT NULL, last_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', code_sha256 VARCHAR(255) DEFAULT NULL, version VARCHAR(255) DEFAULT NULL, dead_letter_config JSON DEFAULT NULL, tracing_config JSON DEFAULT NULL, kms_key_arn VARCHAR(255) DEFAULT NULL, master_arn VARCHAR(255) DEFAULT NULL, revision_id BIGINT DEFAULT NULL, architectures JSON DEFAULT NULL, ephemeral_storage JSON DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, state_reason VARCHAR(255) DEFAULT NULL, state_reason_code VARCHAR(255) DEFAULT NULL, package_type VARCHAR(255) DEFAULT NULL, image_config_response JSON DEFAULT NULL, signing_profile_version_arn VARCHAR(255) DEFAULT NULL, signing_job_arn VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9033040F201B0D8A (crawl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lambda_layer (id INT AUTO_INCREMENT NOT NULL, lambda_function_id INT NOT NULL, arn VARCHAR(255) DEFAULT NULL, code_size BIGINT DEFAULT NULL, signing_profile_version_arn VARCHAR(255) DEFAULT NULL, signing_job_arn VARCHAR(255) DEFAULT NULL, INDEX IDX_371A0DC9B6A1268F (lambda_function_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lambda_vpc_config (id INT AUTO_INCREMENT NOT NULL, lambda_function_id INT NOT NULL, subnet_ids JSON DEFAULT NULL, security_group_ids JSON DEFAULT NULL, vpc_id VARCHAR(255) DEFAULT NULL, ipv6_allowed_for_dual_stack JSON DEFAULT NULL, UNIQUE INDEX UNIQ_18069101B6A1268F (lambda_function_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lambda_environment ADD CONSTRAINT FK_978370ACB6A1268F FOREIGN KEY (lambda_function_id) REFERENCES lambda_function (id)');
        $this->addSql('ALTER TABLE lambda_function ADD CONSTRAINT FK_9033040F201B0D8A FOREIGN KEY (crawl_id) REFERENCES crawl_version (id)');
        $this->addSql('ALTER TABLE lambda_layer ADD CONSTRAINT FK_371A0DC9B6A1268F FOREIGN KEY (lambda_function_id) REFERENCES lambda_function (id)');
        $this->addSql('ALTER TABLE lambda_vpc_config ADD CONSTRAINT FK_18069101B6A1268F FOREIGN KEY (lambda_function_id) REFERENCES lambda_function (id)');
        $this->addSql('ALTER TABLE tag ADD lambda_function_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B783B6A1268F FOREIGN KEY (lambda_function_id) REFERENCES lambda_function (id)');
        $this->addSql('CREATE INDEX IDX_389B783B6A1268F ON tag (lambda_function_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783B6A1268F');
        $this->addSql('ALTER TABLE lambda_environment DROP FOREIGN KEY FK_978370ACB6A1268F');
        $this->addSql('ALTER TABLE lambda_function DROP FOREIGN KEY FK_9033040F201B0D8A');
        $this->addSql('ALTER TABLE lambda_layer DROP FOREIGN KEY FK_371A0DC9B6A1268F');
        $this->addSql('ALTER TABLE lambda_vpc_config DROP FOREIGN KEY FK_18069101B6A1268F');
        $this->addSql('DROP TABLE lambda_environment');
        $this->addSql('DROP TABLE lambda_function');
        $this->addSql('DROP TABLE lambda_layer');
        $this->addSql('DROP TABLE lambda_vpc_config');
        $this->addSql('DROP INDEX IDX_389B783B6A1268F ON tag');
        $this->addSql('ALTER TABLE tag DROP lambda_function_id');
    }
}
