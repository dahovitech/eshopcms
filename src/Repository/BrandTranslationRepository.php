<?php

namespace App\Repository;

use App\Entity\BrandTranslation;
use App\Entity\Language;
use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BrandTranslation>
 */
class BrandTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BrandTranslation::class);
    }

    /**
     * Find translation by brand and language
     */
    public function findByBrandAndLanguage(Brand $brand, Language $language): ?BrandTranslation
    {
        return $this->createQueryBuilder('bt')
            ->where('bt.brand = :brand')
            ->andWhere('bt.language = :language')
            ->setParameter('brand', $brand)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find translations by language code
     */
    public function findByLanguageCode(string $languageCode): array
    {
        return $this->createQueryBuilder('bt')
            ->innerJoin('bt.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->orderBy('bt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find translation by slug and language code (for uniqueness check)
     */
    public function findBySlugTranslation(string $slug, string $languageCode): ?BrandTranslation
    {
        return $this->createQueryBuilder('bt')
            ->innerJoin('bt.language', 'l')
            ->where('bt.slugTranslation = :slug')
            ->andWhere('l.code = :languageCode')
            ->setParameter('slug', $slug)
            ->setParameter('languageCode', $languageCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find incomplete translations (missing name or description)
     */
    public function findIncompleteTranslations(?string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('bt')
            ->where('bt.name = :empty OR bt.description IS NULL OR bt.description = :empty')
            ->setParameter('empty', '');

        if ($languageCode) {
            $qb->innerJoin('bt.language', 'l')
                ->andWhere('l.code = :languageCode')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->orderBy('bt.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count translations by language
     */
    public function countByLanguage(string $languageCode): int
    {
        return $this->createQueryBuilder('bt')
            ->select('COUNT(bt.id)')
            ->innerJoin('bt.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count complete translations by language
     */
    public function countCompleteByLanguage(string $languageCode): int
    {
        return $this->createQueryBuilder('bt')
            ->select('COUNT(bt.id)')
            ->innerJoin('bt.language', 'l')
            ->where('l.code = :languageCode')
            ->andWhere('bt.name != :empty')
            ->andWhere('bt.description IS NOT NULL')
            ->andWhere('bt.description != :empty')
            ->setParameter('languageCode', $languageCode)
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get translation statistics for a specific language
     */
    public function getTranslationStatistics(string $languageCode): array
    {
        $total = $this->countByLanguage($languageCode);
        $complete = $this->countCompleteByLanguage($languageCode);
        $incomplete = $total - $complete;
        $percentage = $total > 0 ? round(($complete / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'complete' => $complete,
            'incomplete' => $incomplete,
            'percentage' => $percentage
        ];
    }

    /**
     * Duplicate translation to another language (as base for translation)
     */
    public function duplicateTranslation(BrandTranslation $sourceTranslation, Language $targetLanguage): BrandTranslation
    {
        $newTranslation = new BrandTranslation();
        $newTranslation->setBrand($sourceTranslation->getBrand());
        $newTranslation->setLanguage($targetLanguage);
        $newTranslation->setName($sourceTranslation->getName());
        $newTranslation->setDescription($sourceTranslation->getDescription());
        $newTranslation->setSlugTranslation($sourceTranslation->getSlugTranslation());
        $newTranslation->setMetaTitle($sourceTranslation->getMetaTitle());
        $newTranslation->setMetaDescription($sourceTranslation->getMetaDescription());

        return $newTranslation;
    }
}