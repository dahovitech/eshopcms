<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928111233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Creating E-commerce tables with translations support
        
        // Skip tables that already exist: languages, media, services, service_translations, user, messenger_messages
        
        // Create Brands table
        $this->addSql('CREATE TABLE brands (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, logo_id INTEGER DEFAULT NULL, slug VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, sort_order INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_7EA24434F98F144A FOREIGN KEY (logo_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EA24434989D9B62 ON brands (slug)');
        $this->addSql('CREATE INDEX IDX_7EA24434F98F144A ON brands (logo_id)');
        
        // Create Brand Translations table
        $this->addSql('CREATE TABLE brand_translations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, brand_id INTEGER NOT NULL, language_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, slug_translation VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(500) DEFAULT NULL, meta_description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_B018D3444F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B018D3482F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B018D3444F5D008 ON brand_translations (brand_id)');
        $this->addSql('CREATE INDEX IDX_B018D3482F1BAF4 ON brand_translations (language_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BRAND_LANGUAGE ON brand_translations (brand_id, language_id)');
        
        // Create Categories table
        $this->addSql('CREATE TABLE categories (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, image_id INTEGER DEFAULT NULL, slug VARCHAR(255) NOT NULL, level INTEGER NOT NULL, is_active BOOLEAN NOT NULL, sort_order INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3AF346683DA5256D FOREIGN KEY (image_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3AF34668989D9B62 ON categories (slug)');
        $this->addSql('CREATE INDEX IDX_3AF34668727ACA70 ON categories (parent_id)');
        $this->addSql('CREATE INDEX IDX_3AF346683DA5256D ON categories (image_id)');
        
        // Create Category Translations table
        $this->addSql('CREATE TABLE category_translations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, language_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, slug_translation VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(500) DEFAULT NULL, meta_description CLOB DEFAULT NULL, meta_keywords CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_1C60F91512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1C60F91582F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1C60F91512469DE2 ON category_translations (category_id)');
        $this->addSql('CREATE INDEX IDX_1C60F91582F1BAF4 ON category_translations (language_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CATEGORY_LANGUAGE ON category_translations (category_id, language_id)');
        
        // Create Attributes table
        $this->addSql('CREATE TABLE attributes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, is_required BOOLEAN NOT NULL, is_variant BOOLEAN NOT NULL, is_filterable BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, sort_order INTEGER NOT NULL, configuration CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_319B9E7077153098 ON attributes (code)');
        
        // Create Attribute Translations table
        $this->addSql('CREATE TABLE attribute_translations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, attribute_id INTEGER NOT NULL, language_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, placeholder VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_4059D4A0B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4059D4A082F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4059D4A0B6E62EFA ON attribute_translations (attribute_id)');
        $this->addSql('CREATE INDEX IDX_4059D4A082F1BAF4 ON attribute_translations (language_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ATTRIBUTE_LANGUAGE ON attribute_translations (attribute_id, language_id)');
        
        // Create Attribute Values table
        $this->addSql('CREATE TABLE attribute_values (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, attribute_id INTEGER NOT NULL, image_id INTEGER DEFAULT NULL, value VARCHAR(255) NOT NULL, hex_color VARCHAR(7) DEFAULT NULL, is_active BOOLEAN NOT NULL, sort_order INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_184662BCB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_184662BC3DA5256D FOREIGN KEY (image_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_184662BCB6E62EFA ON attribute_values (attribute_id)');
        $this->addSql('CREATE INDEX IDX_184662BC3DA5256D ON attribute_values (image_id)');
        
        // Create Attribute Value Translations table
        $this->addSql('CREATE TABLE attribute_value_translations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, attribute_value_id INTEGER NOT NULL, language_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_1293849B65A22152 FOREIGN KEY (attribute_value_id) REFERENCES attribute_values (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1293849B82F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1293849B65A22152 ON attribute_value_translations (attribute_value_id)');
        $this->addSql('CREATE INDEX IDX_1293849B82F1BAF4 ON attribute_value_translations (language_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ATTRIBUTE_VALUE_LANGUAGE ON attribute_value_translations (attribute_value_id, language_id)');
        
        // Create Products table
        $this->addSql('CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, brand_id INTEGER DEFAULT NULL, sku VARCHAR(100) NOT NULL, slug VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, compare_at_price NUMERIC(10, 2) DEFAULT NULL, cost_price NUMERIC(10, 2) DEFAULT NULL, stock INTEGER NOT NULL, low_stock_threshold INTEGER DEFAULT NULL, track_stock BOOLEAN NOT NULL, is_variable BOOLEAN NOT NULL, status VARCHAR(20) NOT NULL, is_digital BOOLEAN NOT NULL, weight NUMERIC(8, 3) DEFAULT NULL, dimensions CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, published_at DATETIME DEFAULT NULL, CONSTRAINT FK_B3BA5A5A44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3BA5A5AF9038C4 ON products (sku)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3BA5A5A989D9B62 ON products (slug)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A44F5D008 ON products (brand_id)');
        
        // Create Product Translations table
        $this->addSql('CREATE TABLE product_translations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, language_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, short_description CLOB DEFAULT NULL, slug_translation VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(500) DEFAULT NULL, meta_description CLOB DEFAULT NULL, meta_keywords CLOB DEFAULT NULL, tags CLOB DEFAULT NULL, specifications VARCHAR(255) DEFAULT NULL, features CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_4B13F8EC4584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4B13F8EC82F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4B13F8EC4584665A ON product_translations (product_id)');
        $this->addSql('CREATE INDEX IDX_4B13F8EC82F1BAF4 ON product_translations (language_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PRODUCT_LANGUAGE ON product_translations (product_id, language_id)');
        
        // Create Product Variants table
        $this->addSql('CREATE TABLE product_variants (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, sku VARCHAR(100) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, compare_at_price NUMERIC(10, 2) DEFAULT NULL, cost_price NUMERIC(10, 2) DEFAULT NULL, stock INTEGER NOT NULL, low_stock_threshold INTEGER DEFAULT NULL, track_stock BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, weight NUMERIC(8, 3) DEFAULT NULL, dimensions CLOB DEFAULT NULL, sort_order INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_782839764584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_78283976F9038C4 ON product_variants (sku)');
        $this->addSql('CREATE INDEX IDX_782839764584665A ON product_variants (product_id)');
        
        // Create Product Variant Translations table
        $this->addSql('CREATE TABLE product_variant_translations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_variant_id INTEGER NOT NULL, language_id INTEGER NOT NULL, name VARCHAR(255) DEFAULT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_60D25BEDA80EF684 FOREIGN KEY (product_variant_id) REFERENCES product_variants (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_60D25BED82F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_60D25BEDA80EF684 ON product_variant_translations (product_variant_id)');
        $this->addSql('CREATE INDEX IDX_60D25BED82F1BAF4 ON product_variant_translations (language_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PRODUCT_VARIANT_LANGUAGE ON product_variant_translations (product_variant_id, language_id)');
        
        // Create Many-to-Many relationship tables
        $this->addSql('CREATE TABLE product_categories (product_id INTEGER NOT NULL, category_id INTEGER NOT NULL, PRIMARY KEY(product_id, category_id), CONSTRAINT FK_A99419434584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A994194312469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A99419434584665A ON product_categories (product_id)');
        $this->addSql('CREATE INDEX IDX_A994194312469DE2 ON product_categories (category_id)');
        
        $this->addSql('CREATE TABLE product_media (product_id INTEGER NOT NULL, media_id INTEGER NOT NULL, PRIMARY KEY(product_id, media_id), CONSTRAINT FK_CB70DA504584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB70DA50EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CB70DA504584665A ON product_media (product_id)');
        $this->addSql('CREATE INDEX IDX_CB70DA50EA9FDD75 ON product_media (media_id)');
        
        $this->addSql('CREATE TABLE product_variant_attribute_values (product_variant_id INTEGER NOT NULL, attribute_value_id INTEGER NOT NULL, PRIMARY KEY(product_variant_id, attribute_value_id), CONSTRAINT FK_8B159D53A80EF684 FOREIGN KEY (product_variant_id) REFERENCES product_variants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8B159D5365A22152 FOREIGN KEY (attribute_value_id) REFERENCES attribute_values (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8B159D53A80EF684 ON product_variant_attribute_values (product_variant_id)');
        $this->addSql('CREATE INDEX IDX_8B159D5365A22152 ON product_variant_attribute_values (attribute_value_id)');
        
        $this->addSql('CREATE TABLE product_variant_media (product_variant_id INTEGER NOT NULL, media_id INTEGER NOT NULL, PRIMARY KEY(product_variant_id, media_id), CONSTRAINT FK_304B22A3A80EF684 FOREIGN KEY (product_variant_id) REFERENCES product_variants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_304B22A3EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_304B22A3A80EF684 ON product_variant_media (product_variant_id)');
        $this->addSql('CREATE INDEX IDX_304B22A3EA9FDD75 ON product_variant_media (media_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // Drop E-commerce tables in reverse order due to foreign key constraints
        $this->addSql('DROP TABLE product_variant_media');
        $this->addSql('DROP TABLE product_variant_attribute_values');
        $this->addSql('DROP TABLE product_media');
        $this->addSql('DROP TABLE product_categories');
        $this->addSql('DROP TABLE product_variant_translations');
        $this->addSql('DROP TABLE product_variants');
        $this->addSql('DROP TABLE product_translations');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE attribute_value_translations');
        $this->addSql('DROP TABLE attribute_values');
        $this->addSql('DROP TABLE attribute_translations');
        $this->addSql('DROP TABLE attributes');
        $this->addSql('DROP TABLE category_translations');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE brand_translations');
        $this->addSql('DROP TABLE brands');
    }
}
