<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240819180025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_menu_item DROP FOREIGN KEY FK_6BD3AEA88D9F6D38');
        $this->addSql('ALTER TABLE order_menu_item DROP FOREIGN KEY FK_6BD3AEA89AB44FE0');
        $this->addSql('DROP TABLE order_menu_item');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D053A935E237E06 ON menu (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_menu_item (order_id INT NOT NULL, menu_item_id INT NOT NULL, INDEX IDX_6BD3AEA88D9F6D38 (order_id), INDEX IDX_6BD3AEA89AB44FE0 (menu_item_id), PRIMARY KEY(order_id, menu_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE order_menu_item ADD CONSTRAINT FK_6BD3AEA88D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_menu_item ADD CONSTRAINT FK_6BD3AEA89AB44FE0 FOREIGN KEY (menu_item_id) REFERENCES menu_item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP INDEX UNIQ_7D053A935E237E06 ON menu');
    }
}
