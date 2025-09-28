<?php

namespace App\Repository;

use App\Entity\ProductVariantTranslation;
use App\Entity\Language;
use App\Entity\ProductVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductVariantTranslation>
 */
class ProductVariantTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariantTranslation::class);
    }

    /**
     * Find translation by product variant and language
     */
    public function findByProductVariantAndLanguage(ProductVariant $productVariant, Language $language): ?ProductVariantTranslation
    {
        return $this->createQueryBuilder('pvt')
            ->where('pvt.productVariant = :productVariant')
            ->andWhere('pvt.language = :language')
            ->setParameter('productVariant', $productVariant)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find translations by language code
     */
    public function findByLanguageCode(string $languageCode): array
    {
        return $this->createQueryBuilder('pvt')
            ->innerJoin('pvt.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->orderBy('pvt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find incomplete translations (missing name)
     */
    public function findIncompleteTranslations(?string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('pvt')
            ->where('pvt.name IS NULL OR pvt.name = :empty')
            ->setParameter('empty', '');

        if ($languageCode) {
            $qb->innerJoin('pvt.language', 'l')
                ->andWhere('l.code = :languageCode')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->orderBy('pvt.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count translations by language
     */
    public function countByLanguage(string $languageCode): int
    {
        return $this->createQueryBuilder('pvt')
            ->select('COUNT(pvt.id)')
            ->innerJoin('pvt.language', 'l')
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
        return $this->createQueryBuilder('pvt')
            ->select('COUNT(pvt.id)')
            ->innerJoin('pvt.language', 'l')
            ->where('l.code = :languageCode')
            ->andWhere('pvt.name IS NOT NULL')
            ->andWhere('pvt.name != :empty')
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
     * Find recent translations (for dashboard)
     */
    public function findRecentTranslations(int $limit = 10): array
    {
        return $this->createQueryBuilder('pvt')
            ->orderBy('pvt.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Duplicate translation to another language (as base for translation)
     */
    public function duplicateTranslation(ProductVariantTranslation $sourceTranslation, Language $targetLanguage): ProductVariantTranslation
    {
        $newTranslation = new ProductVariantTranslation();
        $newTranslation->setProductVariant($sourceTranslation->getProductVariant());
        $newTranslation->setLanguage($targetLanguage);
        $newTranslation->setName($sourceTranslation->getName());
        $newTranslation->setDescription($sourceTranslation->getDescription());

        return $newTranslation;
    }
}