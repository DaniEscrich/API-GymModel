<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250527120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creación de la tabla ia_plan_favorito';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ia_plan_favorito (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            contenido LONGTEXT NOT NULL,
            fecha_guardado DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_IA_PLAN_USER (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE ia_plan_favorito ADD CONSTRAINT FK_IA_PLAN_USER FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ia_plan_favorito');
    }
}
