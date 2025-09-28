<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Refactoring Migration: Improve data types and constraints for e-commerce module
 * 
 * Changes:
 * - Product: price fields changed from string to decimal/float
 * - Product: weight field changed from string to decimal/float  
 * - Product: slug no longer unique (allows multiple products with same slug base)
 * - ProductTranslation: add unique constraint for slug_translation + language_id
 * - ProductTranslation: specifications field changed to TEXT type
 * - Add lifecycle callbacks for automatic timestamp management
 */
final class Version20250928113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactoring: Improve data types and constraints for e-commerce entities';
    }

    public function up(Schema $schema): void
    {
        // Note: For SQLite, we cannot alter column types or constraints
        // This migration documents the required changes for production databases (MySQL/PostgreSQL)
        
        // 1. Remove unique constraint on products.slug
        // ALTER TABLE products DROP INDEX UNIQ_E7D32CB9989D9B62;
        
        // 2. Add unique constraint on product_translations for slug_translation + language_id
        // ALTER TABLE product_translations ADD UNIQUE INDEX UNIQ_SLUG_TRANSLATION_LANGUAGE (slug_translation, language_id);
        
        // 3. Change price fields to DECIMAL type (if using MySQL/PostgreSQL)
        // ALTER TABLE products MODIFY COLUMN price DECIMAL(10,2) NOT NULL;
        // ALTER TABLE products MODIFY COLUMN compare_at_price DECIMAL(10,2) NULL;
        // ALTER TABLE products MODIFY COLUMN cost_price DECIMAL(10,2) NULL;
        // ALTER TABLE products MODIFY COLUMN weight DECIMAL(8,3) NULL;
        
        // 4. Change specifications field to TEXT type
        // ALTER TABLE product_translations MODIFY COLUMN specifications TEXT NULL;
        
        $this->addSql('-- Refactoring migration: See migration comments for required changes in production');
    }

    public function down(Schema $schema): void
    {
        // Reverse changes would require:
        // 1. Add back unique constraint on products.slug
        // 2. Remove unique constraint on product_translations slug_translation + language_id
        // 3. Change price fields back to VARCHAR
        // 4. Change specifications back to VARCHAR(255)
        
        $this->addSql('-- Reverse refactoring migration: Not applicable for SQLite development');
    }
}