<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215222936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, created_by INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, order_number VARCHAR(20) DEFAULT NULL, created_at VARCHAR(20) DEFAULT NULL, INDEX IDX_EAA81A4CA76ED395 (user_id), INDEX IDX_EAA81A4CDE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_balance (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, remaining_value DOUBLE PRECISION NOT NULL, points INT NOT NULL, INDEX IDX_F4F901F4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_balance ADD CONSTRAINT FK_F4F901F4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reward ADD image VARCHAR(255) DEFAULT NULL, ADD status VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CA76ED395');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CDE12AB56');
        $this->addSql('ALTER TABLE user_balance DROP FOREIGN KEY FK_F4F901F4A76ED395');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE user_balance');
        $this->addSql('ALTER TABLE reward DROP image, DROP status, CHANGE description description LONGTEXT NOT NULL');
    }
}
