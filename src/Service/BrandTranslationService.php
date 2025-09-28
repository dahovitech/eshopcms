<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\BrandTranslation;
use App\Repository\BrandTranslationRepository;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class BrandTranslationService
{
    public function __construct(
        private BrandTranslationRepository $brandTranslationRepository,
        private LanguageRepository $languageRepository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {}

    /**
     * Generate unique slug translation for brand
     */
    public function generateUniqueSlugTranslation(string $name, string $languageCode): string
    {
        $baseSlug = $this->slugger->slug($name)->lower();
        $slug = $baseSlug;
        $counter = 1;

        while ($this->brandTranslationRepository->findBySlugTranslation($slug, $languageCode)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Update brand slug translations based on translated names
     */
    public function updateSlugTranslations(Brand $brand): void
    {
        foreach ($brand->getTranslations() as $translation) {
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

    /**
     * Generate brand slug (main brand slug) from default language name
     */
    public function generateBrandSlug(Brand $brand): ?string
    {
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();
        if (!$defaultLanguage) {
            return null;
        }

        $defaultTranslation = $brand->getTranslation($defaultLanguage->getCode());
        if (!$defaultTranslation || empty($defaultTranslation->getName())) {
            return null;
        }

        return $this->generateUniqueMainSlug($defaultTranslation->getName());
    }

    /**
     * Generate unique main slug for brand entity
     */
    public function generateUniqueMainSlug(string $name): string
    {
        $baseSlug = $this->slugger->slug($name)->lower();
        $slug = $baseSlug;
        $counter = 1;

        // Use EntityManager to check brand slug uniqueness
        while ($this->entityManager->getRepository(Brand::class)->findOneBy(['slug' => $slug])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Process brand translation data and generate slugs automatically
     */
    public function processTranslations(Brand $brand, array $translationsData): void
    {
        $languages = $this->languageRepository->findActiveLanguages();

        foreach ($languages as $language) {
            $langCode = $language->getCode();
            if (isset($translationsData[$langCode])) {
                $translationData = $translationsData[$langCode];
                
                if (!empty($translationData['name']) || !empty($translationData['description'])) {
                    $translation = $this->brandTranslationRepository->findOneBy([
                        'brand' => $brand,
                        'language' => $language
                    ]);
                    
                    if (!$translation) {
                        $translation = new BrandTranslation();
                        $translation->setBrand($brand);
                        $translation->setLanguage($language);
                    }

                    // Update translation data
                    if (!empty($translationData['name'])) {
                        $translation->setName($translationData['name']);
                        
                        // Auto-generate slug if not provided or empty
                        if (empty($translationData['slug'])) {
                            $slugTranslation = $this->generateUniqueSlugTranslation(
                                $translationData['name'], 
                                $langCode
                            );
                            $translation->setSlugTranslation($slugTranslation);
                        } else {
                            $translation->setSlugTranslation($translationData['slug']);
                        }
                    }
                    
                    if (!empty($translationData['description'])) {
                        $translation->setDescription($translationData['description']);
                    }

                    $this->entityManager->persist($translation);
                }
            }
        }

        // Generate main brand slug if empty
        if (empty($brand->getSlug())) {
            $mainSlug = $this->generateBrandSlug($brand);
            if ($mainSlug) {
                $brand->setSlug($mainSlug);
            }
        }
    }

    /**
     * Duplicate translation to another language
     */
    public function duplicateTranslation(Brand $brand, string $sourceLanguageCode, string $targetLanguageCode): ?BrandTranslation
    {
        $sourceLanguage = $this->languageRepository->findByCode($sourceLanguageCode);
        $targetLanguage = $this->languageRepository->findByCode($targetLanguageCode);

        if (!$sourceLanguage || !$targetLanguage) {
            return null;
        }

        $sourceTranslation = $brand->getTranslation($sourceLanguageCode);
        if (!$sourceTranslation) {
            return null;
        }

        // Check if target translation already exists
        $existingTranslation = $brand->getTranslation($targetLanguageCode);
        if ($existingTranslation) {
            return $existingTranslation;
        }

        $targetTranslation = new BrandTranslation();
        $targetTranslation->setBrand($brand);
        $targetTranslation->setLanguage($targetLanguage);
        $targetTranslation->setName($sourceTranslation->getName());
        $targetTranslation->setDescription($sourceTranslation->getDescription());
        $targetTranslation->setMetaTitle($sourceTranslation->getMetaTitle());
        $targetTranslation->setMetaDescription($sourceTranslation->getMetaDescription());

        // Generate unique slug for target language
        if (!empty($sourceTranslation->getName())) {
            $slugTranslation = $this->generateUniqueSlugTranslation(
                $sourceTranslation->getName(), 
                $targetLanguageCode
            );
            $targetTranslation->setSlugTranslation($slugTranslation);
        }

        $this->entityManager->persist($targetTranslation);
        $brand->addTranslation($targetTranslation);

        return $targetTranslation;
    }

    /**
     * Create missing translations for all active languages
     */
    public function createMissingTranslations(Brand $brand, ?string $copyFromLanguageCode = null): int
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $created = 0;

        foreach ($languages as $language) {
            if (!$brand->hasTranslation($language->getCode())) {
                if ($copyFromLanguageCode) {
                    $translation = $this->duplicateTranslation($brand, $copyFromLanguageCode, $language->getCode());
                } else {
                    $translation = new BrandTranslation();
                    $translation->setBrand($brand);
                    $translation->setLanguage($language);
                    $this->entityManager->persist($translation);
                    $brand->addTranslation($translation);
                }
                $created++;
            }
        }

        $this->entityManager->flush();
        return $created;
    }

    /**
     * Remove all translations for a language
     */
    public function removeTranslationsForLanguage(string $languageCode): int
    {
        $translations = $this->brandTranslationRepository->findByLanguageCode($languageCode);
        $count = count($translations);

        foreach ($translations as $translation) {
            $this->entityManager->remove($translation);
        }

        $this->entityManager->flush();
        return $count;
    }
}