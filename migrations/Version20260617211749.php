<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617211749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE synchronization (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, error LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, product_id INT NOT NULL, channel_id INT NOT NULL, INDEX IDX_8AF3CC3A4584665A (product_id), INDEX IDX_8AF3CC3A72F5A1AA (channel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE synchronization ADD CONSTRAINT FK_8AF3CC3A4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE synchronization ADD CONSTRAINT FK_8AF3CC3A72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE synchronization DROP FOREIGN KEY FK_8AF3CC3A4584665A');
        $this->addSql('ALTER TABLE synchronization DROP FOREIGN KEY FK_8AF3CC3A72F5A1AA');
        $this->addSql('DROP TABLE synchronization');
    }
}
