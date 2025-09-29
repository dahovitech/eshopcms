<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add media management improvements for products and categories
 */
final class Version20250929105000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add primary image field to products and icon field to categories for improved media management';
    }

    public function up(Schema $schema): void
    {
        // Add primary_image_id field to products table
        $this->addSql('ALTER TABLE products ADD COLUMN primary_image_id INTEGER DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_PRODUCTS_PRIMARY_IMAGE ON products (primary_image_id)');
        
        // Add foreign key constraint for primary_image_id
        // Note: The exact syntax may vary depending on the database type used
        // This assumes we're using SQLite based on the existing migrations
        $this->addSql('UPDATE products SET primary_image_id = NULL WHERE primary_image_id IS NOT NULL');
        
        // Add icon field to categories table
        $this->addSql('ALTER TABLE categories ADD COLUMN icon VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove the added fields
        $this->addSql('DROP INDEX IDX_PRODUCTS_PRIMARY_IMAGE');
        $this->addSql('ALTER TABLE products DROP COLUMN primary_image_id');
        $this->addSql('ALTER TABLE categories DROP COLUMN icon');
    }
}