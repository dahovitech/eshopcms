<?php

namespace App\Repository;

use App\Entity\CategoryTranslation;
use App\Entity\Language;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryTranslation>
 */
class CategoryTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryTranslation::class);
    }

    /**
     * Find translation by category and language
     */
    public function findByCategoryAndLanguage(Category $category, Language $language): ?CategoryTranslation
    {
        return $this->createQueryBuilder('ct')
            ->where('ct.category = :category')
            ->andWhere('ct.language = :language')
            ->setParameter('category', $category)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find translations by language code
     */
    public function findByLanguageCode(string $languageCode): array
    {
        return $this->createQueryBuilder('ct')
            ->innerJoin('ct.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by slug translation
     */
    public function findBySlugTranslation(string $slugTranslation, string $languageCode): ?CategoryTranslation
    {
        return $this->createQueryBuilder('ct')
            ->innerJoin('ct.language', 'l')
            ->where('ct.slugTranslation = :slug')
            ->andWhere('l.code = :languageCode')
            ->setParameter('slug', $slugTranslation)
            ->setParameter('languageCode', $languageCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find incomplete translations (missing name or description)
     */
    public function findIncompleteTranslations(?string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('ct')
            ->where('ct.name = :empty OR ct.description IS NULL OR ct.description = :empty')
            ->setParameter('empty', '');

        if ($languageCode) {
            $qb->innerJoin('ct.language', 'l')
                ->andWhere('l.code = :languageCode')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->orderBy('ct.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count translations by language
     */
    public function countByLanguage(string $languageCode): int
    {
        return $this->createQueryBuilder('ct')
            ->select('COUNT(ct.id)')
            ->innerJoin('ct.language', 'l')
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
        return $this->createQueryBuilder('ct')
            ->select('COUNT(ct.id)')
            ->innerJoin('ct.language', 'l')
            ->where('l.code = :languageCode')
            ->andWhere('ct.name != :empty')
            ->andWhere('ct.description IS NOT NULL')
            ->andWhere('ct.description != :empty')
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
    public function duplicateTranslation(CategoryTranslation $sourceTranslation, Language $targetLanguage): CategoryTranslation
    {
        $newTranslation = new CategoryTranslation();
        $newTranslation->setCategory($sourceTranslation->getCategory());
        $newTranslation->setLanguage($targetLanguage);
        $newTranslation->setName($sourceTranslation->getName());
        $newTranslation->setDescription($sourceTranslation->getDescription());
        $newTranslation->setSlugTranslation($sourceTranslation->getSlugTranslation());
        $newTranslation->setMetaTitle($sourceTranslation->getMetaTitle());
        $newTranslation->setMetaDescription($sourceTranslation->getMetaDescription());
        $newTranslation->setMetaKeywords($sourceTranslation->getMetaKeywords());

        return $newTranslation;
    }
}