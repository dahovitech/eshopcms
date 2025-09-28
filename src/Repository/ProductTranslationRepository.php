<?php

namespace App\Repository;

use App\Entity\ProductTranslation;
use App\Entity\Language;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductTranslation>
 */
class ProductTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductTranslation::class);
    }

    /**
     * Find translation by product and language
     */
    public function findByProductAndLanguage(Product $product, Language $language): ?ProductTranslation
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.product = :product')
            ->andWhere('pt.language = :language')
            ->setParameter('product', $product)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find translations by language code
     */
    public function findByLanguageCode(string $languageCode): array
    {
        return $this->createQueryBuilder('pt')
            ->innerJoin('pt.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->orderBy('pt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by slug translation
     */
    public function findBySlugTranslation(string $slugTranslation, string $languageCode): ?ProductTranslation
    {
        return $this->createQueryBuilder('pt')
            ->innerJoin('pt.language', 'l')
            ->where('pt.slugTranslation = :slug')
            ->andWhere('l.code = :languageCode')
            ->setParameter('slug', $slugTranslation)
            ->setParameter('languageCode', $languageCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find incomplete translations (missing required fields)
     */
    public function findIncompleteTranslations(?string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('pt')
            ->where('pt.name = :empty OR pt.description IS NULL OR pt.description = :empty OR pt.shortDescription IS NULL OR pt.shortDescription = :empty')
            ->setParameter('empty', '');

        if ($languageCode) {
            $qb->innerJoin('pt.language', 'l')
                ->andWhere('l.code = :languageCode')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->orderBy('pt.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count translations by language
     */
    public function countByLanguage(string $languageCode): int
    {
        return $this->createQueryBuilder('pt')
            ->select('COUNT(pt.id)')
            ->innerJoin('pt.language', 'l')
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
        return $this->createQueryBuilder('pt')
            ->select('COUNT(pt.id)')
            ->innerJoin('pt.language', 'l')
            ->where('l.code = :languageCode')
            ->andWhere('pt.name != :empty')
            ->andWhere('pt.description IS NOT NULL')
            ->andWhere('pt.description != :empty')
            ->andWhere('pt.shortDescription IS NOT NULL')
            ->andWhere('pt.shortDescription != :empty')
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
     * Search translations by name or description
     */
    public function searchTranslations(string $query, string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('pt')
            ->where('pt.name LIKE :query OR pt.description LIKE :query OR pt.shortDescription LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        if ($languageCode) {
            $qb->innerJoin('pt.language', 'l')
                ->andWhere('l.code = :languageCode')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->orderBy('pt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent translations (for dashboard)
     */
    public function findRecentTranslations(int $limit = 10): array
    {
        return $this->createQueryBuilder('pt')
            ->orderBy('pt.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Duplicate translation to another language (as base for translation)
     */
    public function duplicateTranslation(ProductTranslation $sourceTranslation, Language $targetLanguage): ProductTranslation
    {
        $newTranslation = new ProductTranslation();
        $newTranslation->setProduct($sourceTranslation->getProduct());
        $newTranslation->setLanguage($targetLanguage);
        $newTranslation->setName($sourceTranslation->getName());
        $newTranslation->setDescription($sourceTranslation->getDescription());
        $newTranslation->setShortDescription($sourceTranslation->getShortDescription());
        $newTranslation->setSlugTranslation($sourceTranslation->getSlugTranslation());
        $newTranslation->setMetaTitle($sourceTranslation->getMetaTitle());
        $newTranslation->setMetaDescription($sourceTranslation->getMetaDescription());
        $newTranslation->setMetaKeywords($sourceTranslation->getMetaKeywords());
        $newTranslation->setTags($sourceTranslation->getTags());
        $newTranslation->setSpecifications($sourceTranslation->getSpecifications());
        $newTranslation->setFeatures($sourceTranslation->getFeatures());

        return $newTranslation;
    }
}