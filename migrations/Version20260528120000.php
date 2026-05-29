<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add system entity flags to users and groups.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "group" ADD is_system BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_system BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "group" DROP is_system');
        $this->addSql('ALTER TABLE "user" DROP is_system');
    }
}
