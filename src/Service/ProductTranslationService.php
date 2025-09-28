<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductTranslationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private ProductTranslationRepository $productTranslationRepository,
        private LanguageRepository $languageRepository,
        private SluggerInterface $slugger
    ) {}

    /**
     * Create or update a product with translations
     */
    public function createOrUpdateProduct(Product $product, array $translationsData): Product
    {
        // Generate slug if not set
        if (empty($product->getSlug()) && !empty($translationsData)) {
            $defaultLang = $this->languageRepository->findDefaultLanguage();
            if (!$defaultLang) {
                throw new \RuntimeException('No default language found in the system.');
            }
            
            $defaultTranslation = $translationsData[$defaultLang->getCode()] ?? reset($translationsData);
            if (!empty($defaultTranslation['name'])) {
                $product->setSlug($this->generateUniqueSlug($defaultTranslation['name']));
            }
        }

        $this->entityManager->persist($product);
        
        // For new products, we need to flush first to get an ID before handling translations
        $isNewProduct = $product->getId() === null;
        if ($isNewProduct) {
            $this->entityManager->flush();
        }

        // Handle translations with validation
        foreach ($translationsData as $languageCode => $data) {
            $language = $this->languageRepository->findByCode($languageCode);
            if (!$language || !$language->isActive()) {
                continue;
            }

            $translation = null;
            if (!$isNewProduct) {
                $translation = $this->productTranslationRepository->findByProductAndLanguage($product, $language);
            }
            
            if (!$translation) {
                $translation = new ProductTranslation();
                $translation->setProduct($product);
                $translation->setLanguage($language);
                $product->addTranslation($translation);
            }

            // Validate required fields
            if (empty($data['name'])) {
                throw new \InvalidArgumentException("Name is required for language {$languageCode}");
            }

            $translation->setName($data['name']);
            $translation->setDescription($data['description'] ?? '');
            $translation->setShortDescription($data['shortDescription'] ?? '');
            $translation->setMetaTitle($data['metaTitle'] ?? null);
            $translation->setMetaDescription($data['metaDescription'] ?? null);
            $translation->setMetaKeywords($data['metaKeywords'] ?? null);
            $translation->setTags($data['tags'] ?? null);
            $translation->setSpecifications($data['specifications'] ?? null);
            $translation->setFeatures($data['features'] ?? null);

            // Auto-generate slug if not provided
            if (empty($data['slugTranslation'])) {
                $translation->generateSlugFromName();
            } else {
                $translation->setSlugTranslation($data['slugTranslation']);
            }

            $this->entityManager->persist($translation);
        }

        $this->entityManager->flush();

        // Validate business rules after persistence
        if (!$product->isValidPriceStructure()) {
            throw new \InvalidArgumentException('Invalid price structure. Check that compareAtPrice > price and costPrice < price.');
        }

        return $product;
    }

    /**
     * Generate unique slug
     */
    public function generateUniqueSlug(string $name): string
    {
        $baseSlug = $this->slugger->slug($name)->lower();
        $slug = $baseSlug;
        $counter = 1;

        while ($this->productRepository->findBySlug($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate unique slug translation
     */
    public function generateUniqueSlugTranslation(string $name, string $languageCode): string
    {
        $baseSlug = $this->slugger->slug($name)->lower();
        $slug = $baseSlug;
        $counter = 1;

        while ($this->productTranslationRepository->findBySlugTranslation($slug, $languageCode)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Duplicate translation to another language
     */
    public function duplicateTranslation(Product $product, string $sourceLanguageCode, string $targetLanguageCode): ?ProductTranslation
    {
        $sourceLanguage = $this->languageRepository->findByCode($sourceLanguageCode);
        $targetLanguage = $this->languageRepository->findByCode($targetLanguageCode);

        if (!$sourceLanguage || !$targetLanguage) {
            return null;
        }

        $sourceTranslation = $product->getTranslation($sourceLanguageCode);
        if (!$sourceTranslation) {
            return null;
        }

        // Check if target translation already exists
        $existingTranslation = $product->getTranslation($targetLanguageCode);
        if ($existingTranslation) {
            return $existingTranslation;
        }

        $newTranslation = $this->productTranslationRepository->duplicateTranslation($sourceTranslation, $targetLanguage);
        $product->addTranslation($newTranslation);
        
        $this->entityManager->persist($newTranslation);
        $this->entityManager->flush();

        return $newTranslation;
    }

    /**
     * Get products with translation status for admin
     */
    public function getProductsWithTranslationStatus(): array
    {
        $products = $this->productRepository->findActiveProducts();
        $languages = $this->languageRepository->findActiveLanguages();
        $result = [];

        foreach ($products as $product) {
            $productData = [
                'product' => $product,
                'translations' => [],
                'completionPercentage' => 0
            ];

            $totalFields = 0;
            $completedFields = 0;

            foreach ($languages as $language) {
                $translation = $product->getTranslation($language->getCode());
                $status = [
                    'language' => $language,
                    'translation' => $translation,
                    'complete' => false,
                    'partial' => false,
                    'missing' => true
                ];

                if ($translation) {
                    $status['missing'] = false;
                    $status['complete'] = $translation->isComplete();
                    $status['partial'] = $translation->isPartial();
                    
                    // Count fields for completion percentage
                    $totalFields += 10; // name, description, shortDescription, metaTitle, metaDescription, slugTranslation, metaKeywords, tags, specifications, features
                    $completedFields += array_sum([
                        !empty($translation->getName()),
                        !empty($translation->getDescription()),
                        !empty($translation->getShortDescription()),
                        !empty($translation->getMetaTitle()),
                        !empty($translation->getMetaDescription()),
                        !empty($translation->getSlugTranslation()),
                        !empty($translation->getMetaKeywords()),
                        !empty($translation->getTags()),
                        !empty($translation->getSpecifications()),
                        !empty($translation->getFeatures())
                    ]);
                } else {
                    $totalFields += 10;
                }

                $productData['translations'][$language->getCode()] = $status;
            }

            $productData['completionPercentage'] = $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
            $result[] = $productData;
        }

        return $result;
    }

    /**
     * Get global translation statistics
     */
    public function getGlobalTranslationStatistics(): array
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $totalProducts = count($this->productRepository->findActiveProducts());
        $statistics = [];

        foreach ($languages as $language) {
            $stats = $this->productTranslationRepository->getTranslationStatistics($language->getCode());
            $missing = $totalProducts - $stats['total'];
            
            $statistics[$language->getCode()] = [
                'language' => $language,
                'total_products' => $totalProducts,
                'translated' => $stats['total'],
                'complete' => $stats['complete'],
                'incomplete' => $stats['incomplete'],
                'missing' => $missing,
                'completion_percentage' => $stats['percentage']
            ];
        }

        return $statistics;
    }

    /**
     * Create missing translations for all products in a language
     */
    public function createMissingTranslations(string $languageCode, ?string $sourceLanguageCode = null): int
    {
        $language = $this->languageRepository->findByCode($languageCode);
        if (!$language) {
            return 0;
        }

        $sourceLanguage = null;
        if ($sourceLanguageCode) {
            $sourceLanguage = $this->languageRepository->findByCode($sourceLanguageCode);
        }
        
        if (!$sourceLanguage) {
            $sourceLanguage = $this->languageRepository->findDefaultLanguage();
        }

        $products = $this->productRepository->findActiveProducts();
        $created = 0;

        foreach ($products as $product) {
            // Skip if translation already exists
            if ($product->hasTranslation($languageCode)) {
                continue;
            }

            $translation = new ProductTranslation();
            $translation->setProduct($product);
            $translation->setLanguage($language);

            // Copy from source language if available
            if ($sourceLanguage) {
                $sourceTranslation = $product->getTranslation($sourceLanguage->getCode());
                if ($sourceTranslation) {
                    $translation->setName($sourceTranslation->getName());
                    $translation->setDescription($sourceTranslation->getDescription());
                    $translation->setShortDescription($sourceTranslation->getShortDescription());
                    $translation->setSlugTranslation($sourceTranslation->getSlugTranslation());
                    $translation->setMetaTitle($sourceTranslation->getMetaTitle());
                    $translation->setMetaDescription($sourceTranslation->getMetaDescription());
                    $translation->setMetaKeywords($sourceTranslation->getMetaKeywords());
                    $translation->setTags($sourceTranslation->getTags());
                    $translation->setSpecifications($sourceTranslation->getSpecifications());
                    $translation->setFeatures($sourceTranslation->getFeatures());
                }
            }

            $this->entityManager->persist($translation);
            $product->addTranslation($translation);
            $created++;
        }

        $this->entityManager->flush();
        return $created;
    }

    /**
     * Remove all translations for a language
     */
    public function removeTranslationsForLanguage(string $languageCode): int
    {
        $translations = $this->productTranslationRepository->findByLanguageCode($languageCode);
        $count = count($translations);

        foreach ($translations as $translation) {
            $this->entityManager->remove($translation);
        }

        $this->entityManager->flush();
        return $count;
    }

    /**
     * Update product slug translations based on translated names
     */
    public function updateSlugTranslations(Product $product): void
    {
        foreach ($product->getTranslations() as $translation) {
            if (!empty($translation->getName()) && empty($translation->getSlugTranslation())) {
                $slugTranslation = $this->generateUniqueSlugTranslation(
                    $translation->getName(), 
                    $translation->getLanguage()->getCode()
                );
                $translation->setSlugTranslation($slugTranslation);
                $translation->setUpdatedAt();
            }
        }

        $this->entityManager->flush();
    }
}