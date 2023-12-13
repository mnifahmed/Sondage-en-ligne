<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231202144054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, wording VARCHAR(255) NOT NULL, INDEX IDX_DADD4A251E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE choice (id INT AUTO_INCREMENT NOT NULL, participation_id INT NOT NULL, question_id INT NOT NULL, answer_id INT NOT NULL, INDEX IDX_C1AB5A926ACE3B73 (participation_id), INDEX IDX_C1AB5A921E27F6BF (question_id), INDEX IDX_C1AB5A92AA334807 (answer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, poll_id INT NOT NULL, date DATETIME NOT NULL, finished TINYINT(1) NOT NULL DEFAULT 0, INDEX IDX_AB55E24FA76ED395 (user_id), INDEX IDX_AB55E24F3C947C0F (poll_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poll (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, poll_id INT NOT NULL, title VARCHAR(255) NOT NULL, ordre INT NOT NULL, INDEX IDX_B6F7494E3C947C0F (poll_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL DEFAULT \'regular\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A926ACE3B73 FOREIGN KEY (participation_id) REFERENCES participation (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A921E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A926ACE3B73');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A921E27F6BF');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A92AA334807');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FA76ED395');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F3C947C0F');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E3C947C0F');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE choice');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
