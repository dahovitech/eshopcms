<?php

namespace App\Service;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\BrandTranslationRepository;
use App\Repository\CategoryTranslationRepository;
use App\Repository\AttributeTranslationRepository;
use App\Repository\AttributeValueTranslationRepository;
use App\Repository\ProductTranslationRepository;
use App\Repository\ProductVariantTranslationRepository;
use App\Repository\ServiceTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;

class EcommerceTranslationService
{
    private array $entityTypes = [
        'brands' => 'BrandTranslation',
        'categories' => 'CategoryTranslation',
        'attributes' => 'AttributeTranslation',
        'attribute_values' => 'AttributeValueTranslation',
        'products' => 'ProductTranslation',
        'product_variants' => 'ProductVariantTranslation',
        'services' => 'ServiceTranslation'
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LanguageRepository $languageRepository,
        private BrandTranslationRepository $brandTranslationRepository,
        private CategoryTranslationRepository $categoryTranslationRepository,
        private AttributeTranslationRepository $attributeTranslationRepository,
        private AttributeValueTranslationRepository $attributeValueTranslationRepository,
        private ProductTranslationRepository $productTranslationRepository,
        private ProductVariantTranslationRepository $productVariantTranslationRepository,
        private ServiceTranslationRepository $serviceTranslationRepository
    ) {}

    /**
     * Get global e-commerce translation statistics for all entity types
     */
    public function getGlobalEcommerceTranslationStatistics(): array
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $statistics = [];

        foreach ($languages as $language) {
            $languageCode = $language->getCode();
            $languageStats = [
                'language' => $language,
                'entities' => [],
                'totals' => [
                    'total' => 0,
                    'complete' => 0,
                    'incomplete' => 0,
                    'percentage' => 0
                ]
            ];

            $totalComplete = 0;
            $totalTranslations = 0;

            // Get statistics for each entity type
            foreach ($this->entityTypes as $entityKey => $entityClass) {
                $repository = $this->getRepositoryForEntity($entityKey);
                if ($repository && method_exists($repository, 'getTranslationStatistics')) {
                    $stats = $repository->getTranslationStatistics($languageCode);
                    $languageStats['entities'][$entityKey] = $stats;
                    
                    $totalComplete += $stats['complete'];
                    $totalTranslations += $stats['total'];
                }
            }

            // Calculate overall percentage
            $languageStats['totals'] = [
                'total' => $totalTranslations,
                'complete' => $totalComplete,
                'incomplete' => $totalTranslations - $totalComplete,
                'percentage' => $totalTranslations > 0 ? round(($totalComplete / $totalTranslations) * 100, 1) : 0
            ];

            $statistics[$languageCode] = $languageStats;
        }

        return $statistics;
    }

    /**
     * Get translation statistics for a specific entity type
     */
    public function getEntityTranslationStatistics(string $entityType): array
    {
        if (!isset($this->entityTypes[$entityType])) {
            throw new \InvalidArgumentException("Unknown entity type: {$entityType}");
        }

        $languages = $this->languageRepository->findActiveLanguages();
        $statistics = [];
        $repository = $this->getRepositoryForEntity($entityType);

        if (!$repository || !method_exists($repository, 'getTranslationStatistics')) {
            return $statistics;
        }

        foreach ($languages as $language) {
            $stats = $repository->getTranslationStatistics($language->getCode());
            $statistics[$language->getCode()] = [
                'language' => $language,
                'stats' => $stats
            ];
        }

        return $statistics;
    }

    /**
     * Create missing translations for all entities in a specific language
     */
    public function createMissingTranslationsForAllEntities(string $languageCode, ?string $sourceLanguageCode = null): array
    {
        $language = $this->languageRepository->findByCode($languageCode);
        if (!$language) {
            throw new \InvalidArgumentException("Language not found: {$languageCode}");
        }

        $sourceLanguage = null;
        if ($sourceLanguageCode) {
            $sourceLanguage = $this->languageRepository->findByCode($sourceLanguageCode);
        }
        
        if (!$sourceLanguage) {
            $sourceLanguage = $this->languageRepository->findDefaultLanguage();
        }

        $results = [];

        // Create missing translations for each entity type
        $entityServices = $this->getTranslationServices();
        
        foreach ($entityServices as $entityType => $service) {
            if (method_exists($service, 'createMissingTranslations')) {
                try {
                    $created = $service->createMissingTranslations($languageCode, $sourceLanguage?->getCode());
                    $results[$entityType] = [
                        'success' => true,
                        'created' => $created,
                        'message' => "Created {$created} missing translations for {$entityType}"
                    ];
                } catch (\Exception $e) {
                    $results[$entityType] = [
                        'success' => false,
                        'created' => 0,
                        'message' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Synchronize translations with available languages
     */
    public function synchronizeTranslationsWithLanguages(): array
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();
        $results = [];

        if (!$defaultLanguage) {
            throw new \RuntimeException('No default language found');
        }

        foreach ($languages as $language) {
            if ($language->getCode() === $defaultLanguage->getCode()) {
                continue;
            }

            $languageResults = $this->createMissingTranslationsForAllEntities(
                $language->getCode(),
                $defaultLanguage->getCode()
            );

            $results[$language->getCode()] = [
                'language' => $language,
                'entities' => $languageResults
            ];
        }

        return $results;
    }

    /**
     * Get incomplete translations across all entity types for a language
     */
    public function getIncompleteTranslations(string $languageCode): array
    {
        $incompleteTranslations = [];

        foreach ($this->entityTypes as $entityKey => $entityClass) {
            $repository = $this->getRepositoryForEntity($entityKey);
            if ($repository && method_exists($repository, 'findIncompleteTranslations')) {
                $incomplete = $repository->findIncompleteTranslations($languageCode);
                if (!empty($incomplete)) {
                    $incompleteTranslations[$entityKey] = $incomplete;
                }
            }
        }

        return $incompleteTranslations;
    }

    /**
     * Remove all translations for a language across all entity types
     */
    public function removeTranslationsForLanguage(string $languageCode): array
    {
        $results = [];
        $entityServices = $this->getTranslationServices();

        foreach ($entityServices as $entityType => $service) {
            if (method_exists($service, 'removeTranslationsForLanguage')) {
                try {
                    $removed = $service->removeTranslationsForLanguage($languageCode);
                    $results[$entityType] = [
                        'success' => true,
                        'removed' => $removed,
                        'message' => "Removed {$removed} translations for {$entityType}"
                    ];
                } catch (\Exception $e) {
                    $results[$entityType] = [
                        'success' => false,
                        'removed' => 0,
                        'message' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Get repository for a specific entity type
     */
    private function getRepositoryForEntity(string $entityType): ?object
    {
        return match($entityType) {
            'brands' => $this->brandTranslationRepository,
            'categories' => $this->categoryTranslationRepository,
            'attributes' => $this->attributeTranslationRepository,
            'attribute_values' => $this->attributeValueTranslationRepository,
            'products' => $this->productTranslationRepository,
            'product_variants' => $this->productVariantTranslationRepository,
            'services' => $this->serviceTranslationRepository,
            default => null
        };
    }

    /**
     * Get translation services for each entity type
     */
    private function getTranslationServices(): array
    {
        // Note: These services would need to be injected or retrieved from container
        // For now, this is a placeholder that shows the structure
        return [
            'brands' => null, // BrandTranslationService would be injected here
            'categories' => null, // CategoryTranslationService would be injected here  
            'attributes' => null, // AttributeTranslationService would be injected here
            'attribute_values' => null, // AttributeValueTranslationService would be injected here
            'products' => null, // ProductTranslationService would be injected here
            'product_variants' => null, // ProductVariantTranslationService would be injected here
            'services' => null // ServiceTranslationService is already available
        ];
    }

    /**
     * Get translation progress summary
     */
    public function getTranslationProgressSummary(): array
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();
        
        $summary = [
            'languages' => count($languages),
            'default_language' => $defaultLanguage?->getCode(),
            'entity_types' => count($this->entityTypes),
            'overall_progress' => []
        ];

        $totalComplete = 0;
        $totalTranslations = 0;

        foreach ($languages as $language) {
            if ($language->getCode() === $defaultLanguage?->getCode()) {
                continue;
            }

            $languageStats = $this->getGlobalEcommerceTranslationStatistics()[$language->getCode()] ?? null;
            if ($languageStats) {
                $totalComplete += $languageStats['totals']['complete'];
                $totalTranslations += $languageStats['totals']['total'];
                
                $summary['overall_progress'][$language->getCode()] = [
                    'language' => $language->getName(),
                    'percentage' => $languageStats['totals']['percentage']
                ];
            }
        }

        $summary['overall_percentage'] = $totalTranslations > 0 ? round(($totalComplete / $totalTranslations) * 100, 1) : 0;

        return $summary;
    }
}