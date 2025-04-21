<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408173354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE redemption_request ADD processed_date DATETIME DEFAULT NULL, ADD points_cost INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transactions ADD is_consumed TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_balance ADD reserved_points INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_balance DROP reserved_points
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transactions DROP is_consumed
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE redemption_request DROP processed_date, DROP points_cost
        SQL);
    }
}
