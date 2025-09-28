<?php

namespace App\DataFixtures;

use App\Entity\Language;
use App\Entity\Brand;
use App\Entity\BrandTranslation;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Attribute;
use App\Entity\AttributeTranslation;
use App\Entity\AttributeValue;
use App\Entity\AttributeValueTranslation;
use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\ProductVariant;
use App\Entity\ProductVariantTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EcommerceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Languages
        $languages = $this->createLanguages($manager);
        
        // Create Brands
        $brands = $this->createBrands($manager, $languages);
        
        // Create Categories
        $categories = $this->createCategories($manager, $languages);
        
        // Create Attributes
        $attributes = $this->createAttributes($manager, $languages);
        
        // Create Products
        $this->createProducts($manager, $languages, $brands, $categories, $attributes);
        
        $manager->flush();
    }

    private function createLanguages(ObjectManager $manager): array
    {
        $languagesData = [
            ['fr', 'Français', 'Français', true, true, 0],
            ['en', 'English', 'English', true, false, 1],
            ['es', 'Español', 'Español', true, false, 2],
            ['de', 'Deutsch', 'Deutsch', true, false, 3]
        ];

        $languages = [];
        foreach ($languagesData as [$code, $name, $nativeName, $isActive, $isDefault, $sortOrder]) {
            $language = new Language();
            $language->setCode($code);
            $language->setName($name);
            $language->setNativeName($nativeName);
            $language->setIsActive($isActive);
            $language->setIsDefault($isDefault);
            $language->setSortOrder($sortOrder);
            
            $manager->persist($language);
            $languages[$code] = $language;
        }

        return $languages;
    }

    private function createBrands(ObjectManager $manager, array $languages): array
    {
        $brandsData = [
            [
                'slug' => 'apple',
                'translations' => [
                    'fr' => ['name' => 'Apple', 'description' => 'Marque technologique innovante', 'slugTranslation' => 'apple'],
                    'en' => ['name' => 'Apple', 'description' => 'Innovative technology brand', 'slugTranslation' => 'apple'],
                    'es' => ['name' => 'Apple', 'description' => 'Marca tecnológica innovadora', 'slugTranslation' => 'apple'],
                    'de' => ['name' => 'Apple', 'description' => 'Innovative Technologiemarke', 'slugTranslation' => 'apple']
                ]
            ],
            [
                'slug' => 'samsung',
                'translations' => [
                    'fr' => ['name' => 'Samsung', 'description' => 'Leader mondial de la technologie', 'slugTranslation' => 'samsung'],
                    'en' => ['name' => 'Samsung', 'description' => 'Global technology leader', 'slugTranslation' => 'samsung'],
                    'es' => ['name' => 'Samsung', 'description' => 'Líder mundial en tecnología', 'slugTranslation' => 'samsung'],
                    'de' => ['name' => 'Samsung', 'description' => 'Weltweiter Technologieführer', 'slugTranslation' => 'samsung']
                ]
            ]
        ];

        $brands = [];
        foreach ($brandsData as $brandData) {
            $brand = new Brand();
            $brand->setSlug($brandData['slug']);
            $brand->setIsActive(true);
            $brand->setSortOrder(0);
            
            foreach ($brandData['translations'] as $langCode => $translationData) {
                $language = $languages[$langCode];
                $translation = new BrandTranslation();
                $translation->setBrand($brand);
                $translation->setLanguage($language);
                $translation->setName($translationData['name']);
                $translation->setDescription($translationData['description']);
                $translation->setSlugTranslation($translationData['slugTranslation']);
                
                $brand->addTranslation($translation);
                $manager->persist($translation);
            }
            
            $manager->persist($brand);
            $brands[$brandData['slug']] = $brand;
        }

        return $brands;
    }

    private function createCategories(ObjectManager $manager, array $languages): array
    {
        $categoriesData = [
            [
                'slug' => 'electronics',
                'level' => 0,
                'parent' => null,
                'translations' => [
                    'fr' => ['name' => 'Électronique', 'description' => 'Appareils électroniques et gadgets', 'slugTranslation' => 'electronique'],
                    'en' => ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets', 'slugTranslation' => 'electronics'],
                    'es' => ['name' => 'Electrónicos', 'description' => 'Dispositivos electrónicos y gadgets', 'slugTranslation' => 'electronicos'],
                    'de' => ['name' => 'Elektronik', 'description' => 'Elektronische Geräte und Gadgets', 'slugTranslation' => 'elektronik']
                ]
            ],
            [
                'slug' => 'smartphones',
                'level' => 1,
                'parent' => 'electronics',
                'translations' => [
                    'fr' => ['name' => 'Smartphones', 'description' => 'Téléphones intelligents dernière génération', 'slugTranslation' => 'smartphones'],
                    'en' => ['name' => 'Smartphones', 'description' => 'Latest generation smart phones', 'slugTranslation' => 'smartphones'],
                    'es' => ['name' => 'Smartphones', 'description' => 'Teléfonos inteligentes de última generación', 'slugTranslation' => 'smartphones'],
                    'de' => ['name' => 'Smartphones', 'description' => 'Neueste Generation von Smartphones', 'slugTranslation' => 'smartphones']
                ]
            ]
        ];

        $categories = [];
        foreach ($categoriesData as $categoryData) {
            $category = new Category();
            $category->setSlug($categoryData['slug']);
            $category->setLevel($categoryData['level']);
            $category->setIsActive(true);
            $category->setSortOrder(0);
            
            if ($categoryData['parent']) {
                $category->setParent($categories[$categoryData['parent']]);
            }
            
            foreach ($categoryData['translations'] as $langCode => $translationData) {
                $language = $languages[$langCode];
                $translation = new CategoryTranslation();
                $translation->setCategory($category);
                $translation->setLanguage($language);
                $translation->setName($translationData['name']);
                $translation->setDescription($translationData['description']);
                $translation->setSlugTranslation($translationData['slugTranslation']);
                
                $category->addTranslation($translation);
                $manager->persist($translation);
            }
            
            $manager->persist($category);
            $categories[$categoryData['slug']] = $category;
        }

        return $categories;
    }

    private function createAttributes(ObjectManager $manager, array $languages): array
    {
        $attributesData = [
            [
                'code' => 'color',
                'type' => Attribute::TYPE_COLOR,
                'isVariant' => true,
                'translations' => [
                    'fr' => ['name' => 'Couleur', 'description' => 'La couleur du produit'],
                    'en' => ['name' => 'Color', 'description' => 'The product color'],
                    'es' => ['name' => 'Color', 'description' => 'El color del producto'],
                    'de' => ['name' => 'Farbe', 'description' => 'Die Produktfarbe']
                ],
                'values' => [
                    ['value' => 'red', 'hexColor' => '#FF0000', 'translations' => [
                        'fr' => ['name' => 'Rouge'],
                        'en' => ['name' => 'Red'],
                        'es' => ['name' => 'Rojo'],
                        'de' => ['name' => 'Rot']
                    ]],
                    ['value' => 'blue', 'hexColor' => '#0000FF', 'translations' => [
                        'fr' => ['name' => 'Bleu'],
                        'en' => ['name' => 'Blue'],
                        'es' => ['name' => 'Azul'],
                        'de' => ['name' => 'Blau']
                    ]],
                    ['value' => 'black', 'hexColor' => '#000000', 'translations' => [
                        'fr' => ['name' => 'Noir'],
                        'en' => ['name' => 'Black'],
                        'es' => ['name' => 'Negro'],
                        'de' => ['name' => 'Schwarz']
                    ]]
                ]
            ],
            [
                'code' => 'storage',
                'type' => Attribute::TYPE_SELECT,
                'isVariant' => true,
                'translations' => [
                    'fr' => ['name' => 'Stockage', 'description' => 'Capacité de stockage'],
                    'en' => ['name' => 'Storage', 'description' => 'Storage capacity'],
                    'es' => ['name' => 'Almacenamiento', 'description' => 'Capacidad de almacenamiento'],
                    'de' => ['name' => 'Speicher', 'description' => 'Speicherkapazität']
                ],
                'values' => [
                    ['value' => '64gb', 'translations' => [
                        'fr' => ['name' => '64 Go'],
                        'en' => ['name' => '64 GB'],
                        'es' => ['name' => '64 GB'],
                        'de' => ['name' => '64 GB']
                    ]],
                    ['value' => '128gb', 'translations' => [
                        'fr' => ['name' => '128 Go'],
                        'en' => ['name' => '128 GB'],
                        'es' => ['name' => '128 GB'],
                        'de' => ['name' => '128 GB']
                    ]],
                    ['value' => '256gb', 'translations' => [
                        'fr' => ['name' => '256 Go'],
                        'en' => ['name' => '256 GB'],
                        'es' => ['name' => '256 GB'],
                        'de' => ['name' => '256 GB']
                    ]]
                ]
            ]
        ];

        $attributes = [];
        foreach ($attributesData as $attributeData) {
            $attribute = new Attribute();
            $attribute->setCode($attributeData['code']);
            $attribute->setType($attributeData['type']);
            $attribute->setIsVariant($attributeData['isVariant']);
            $attribute->setIsRequired(false);
            $attribute->setIsFilterable(true);
            $attribute->setIsActive(true);
            $attribute->setSortOrder(0);
            
            foreach ($attributeData['translations'] as $langCode => $translationData) {
                $language = $languages[$langCode];
                $translation = new AttributeTranslation();
                $translation->setAttribute($attribute);
                $translation->setLanguage($language);
                $translation->setName($translationData['name']);
                $translation->setDescription($translationData['description'] ?? '');
                
                $attribute->addTranslation($translation);
                $manager->persist($translation);
            }
            
            // Create attribute values
            $attributeValues = [];
            foreach ($attributeData['values'] as $valueData) {
                $attributeValue = new AttributeValue();
                $attributeValue->setAttribute($attribute);
                $attributeValue->setValue($valueData['value']);
                $attributeValue->setHexColor($valueData['hexColor'] ?? null);
                $attributeValue->setIsActive(true);
                $attributeValue->setSortOrder(0);
                
                foreach ($valueData['translations'] as $langCode => $translationData) {
                    $language = $languages[$langCode];
                    $valueTranslation = new AttributeValueTranslation();
                    $valueTranslation->setAttributeValue($attributeValue);
                    $valueTranslation->setLanguage($language);
                    $valueTranslation->setName($translationData['name']);
                    
                    $attributeValue->addTranslation($valueTranslation);
                    $manager->persist($valueTranslation);
                }
                
                $attribute->addValue($attributeValue);
                $manager->persist($attributeValue);
                $attributeValues[] = $attributeValue;
            }
            
            $manager->persist($attribute);
            $attributes[$attributeData['code']] = [
                'attribute' => $attribute,
                'values' => $attributeValues
            ];
        }

        return $attributes;
    }

    private function createProducts(ObjectManager $manager, array $languages, array $brands, array $categories, array $attributes): void
    {
        $productsData = [
            [
                'sku' => 'IPHONE-15-PRO',
                'slug' => 'iphone-15-pro',
                'price' => '1199.00',
                'brand' => 'apple',
                'category' => 'smartphones',
                'isVariable' => true,
                'translations' => [
                    'fr' => [
                        'name' => 'iPhone 15 Pro',
                        'description' => 'Le smartphone le plus avancé d\'Apple avec une puce A17 Pro révolutionnaire.',
                        'shortDescription' => 'iPhone 15 Pro avec puce A17 Pro',
                        'slugTranslation' => 'iphone-15-pro'
                    ],
                    'en' => [
                        'name' => 'iPhone 15 Pro',
                        'description' => 'Apple\'s most advanced smartphone with revolutionary A17 Pro chip.',
                        'shortDescription' => 'iPhone 15 Pro with A17 Pro chip',
                        'slugTranslation' => 'iphone-15-pro'
                    ],
                    'es' => [
                        'name' => 'iPhone 15 Pro',
                        'description' => 'El smartphone más avanzado de Apple con el revolucionario chip A17 Pro.',
                        'shortDescription' => 'iPhone 15 Pro con chip A17 Pro',
                        'slugTranslation' => 'iphone-15-pro'
                    ],
                    'de' => [
                        'name' => 'iPhone 15 Pro',
                        'description' => 'Apples fortschrittlichstes Smartphone mit revolutionärem A17 Pro Chip.',
                        'shortDescription' => 'iPhone 15 Pro mit A17 Pro Chip',
                        'slugTranslation' => 'iphone-15-pro'
                    ]
                ],
                'variants' => [
                    [
                        'sku' => 'IPHONE-15-PRO-BLACK-128GB',
                        'attributeValues' => ['black', '128gb'],
                        'price' => '1199.00',
                        'stock' => 50,
                        'translations' => [
                            'fr' => ['name' => 'iPhone 15 Pro Noir 128Go'],
                            'en' => ['name' => 'iPhone 15 Pro Black 128GB'],
                            'es' => ['name' => 'iPhone 15 Pro Negro 128GB'],
                            'de' => ['name' => 'iPhone 15 Pro Schwarz 128GB']
                        ]
                    ],
                    [
                        'sku' => 'IPHONE-15-PRO-BLUE-256GB',
                        'attributeValues' => ['blue', '256gb'],
                        'price' => '1399.00',
                        'stock' => 30,
                        'translations' => [
                            'fr' => ['name' => 'iPhone 15 Pro Bleu 256Go'],
                            'en' => ['name' => 'iPhone 15 Pro Blue 256GB'],
                            'es' => ['name' => 'iPhone 15 Pro Azul 256GB'],
                            'de' => ['name' => 'iPhone 15 Pro Blau 256GB']
                        ]
                    ]
                ]
            ]
        ];

        foreach ($productsData as $productData) {
            $product = new Product();
            $product->setSku($productData['sku']);
            $product->setSlug($productData['slug']);
            $product->setPrice($productData['price']);
            $product->setBrand($brands[$productData['brand']]);
            $product->addCategory($categories[$productData['category']]);
            $product->setIsVariable($productData['isVariable']);
            $product->setStatus(Product::STATUS_ACTIVE);
            $product->setIsDigital(false);
            $product->setTrackStock(true);
            $product->setStock(0); // Variable product stock is handled by variants
            $product->setPublishedAt(new \DateTimeImmutable());
            
            foreach ($productData['translations'] as $langCode => $translationData) {
                $language = $languages[$langCode];
                $translation = new ProductTranslation();
                $translation->setProduct($product);
                $translation->setLanguage($language);
                $translation->setName($translationData['name']);
                $translation->setDescription($translationData['description']);
                $translation->setShortDescription($translationData['shortDescription']);
                $translation->setSlugTranslation($translationData['slugTranslation']);
                
                $product->addTranslation($translation);
                $manager->persist($translation);
            }
            
            // Create variants if variable product
            if ($productData['isVariable'] && isset($productData['variants'])) {
                foreach ($productData['variants'] as $variantData) {
                    $variant = new ProductVariant();
                    $variant->setProduct($product);
                    $variant->setSku($variantData['sku']);
                    $variant->setPrice($variantData['price']);
                    $variant->setStock($variantData['stock']);
                    $variant->setIsActive(true);
                    $variant->setTrackStock(true);
                    $variant->setSortOrder(0);
                    
                    // Add attribute values
                    foreach ($variantData['attributeValues'] as $attributeValueCode) {
                        foreach ($attributes as $attributeData) {
                            foreach ($attributeData['values'] as $attributeValue) {
                                if ($attributeValue->getValue() === $attributeValueCode) {
                                    $variant->addAttributeValue($attributeValue);
                                    break 2;
                                }
                            }
                        }
                    }
                    
                    // Add variant translations
                    foreach ($variantData['translations'] as $langCode => $translationData) {
                        $language = $languages[$langCode];
                        $variantTranslation = new ProductVariantTranslation();
                        $variantTranslation->setProductVariant($variant);
                        $variantTranslation->setLanguage($language);
                        $variantTranslation->setName($translationData['name']);
                        
                        $variant->addTranslation($variantTranslation);
                        $manager->persist($variantTranslation);
                    }
                    
                    $product->addVariant($variant);
                    $manager->persist($variant);
                }
            }
            
            $manager->persist($product);
        }
    }
}