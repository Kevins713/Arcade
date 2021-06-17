<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210617093001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum ADD author_id INT NOT NULL');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECDF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_852BBECDF675F31B ON forum (author_id)');
        $this->addSql('ALTER TABLE sub_category ADD forum_id INT NOT NULL');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F79829CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id)');
        $this->addSql('CREATE INDEX IDX_BCE3F79829CCBAD0 ON sub_category (forum_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECDF675F31B');
        $this->addSql('DROP INDEX IDX_852BBECDF675F31B ON forum');
        $this->addSql('ALTER TABLE forum DROP author_id');
        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F79829CCBAD0');
        $this->addSql('DROP INDEX IDX_BCE3F79829CCBAD0 ON sub_category');
        $this->addSql('ALTER TABLE sub_category DROP forum_id');
    }
}
