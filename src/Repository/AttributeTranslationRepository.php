<?php

namespace App\Repository;

use App\Entity\AttributeTranslation;
use App\Entity\Language;
use App\Entity\Attribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AttributeTranslation>
 */
class AttributeTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeTranslation::class);
    }

    /**
     * Find translation by attribute and language
     */
    public function findByAttributeAndLanguage(Attribute $attribute, Language $language): ?AttributeTranslation
    {
        return $this->createQueryBuilder('at')
            ->where('at.attribute = :attribute')
            ->andWhere('at.language = :language')
            ->setParameter('attribute', $attribute)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find translations by language code
     */
    public function findByLanguageCode(string $languageCode): array
    {
        return $this->createQueryBuilder('at')
            ->innerJoin('at.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->orderBy('at.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find incomplete translations (missing name)
     */
    public function findIncompleteTranslations(?string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('at')
            ->where('at.name = :empty')
            ->setParameter('empty', '');

        if ($languageCode) {
            $qb->innerJoin('at.language', 'l')
                ->andWhere('l.code = :languageCode')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->orderBy('at.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count translations by language
     */
    public function countByLanguage(string $languageCode): int
    {
        return $this->createQueryBuilder('at')
            ->select('COUNT(at.id)')
            ->innerJoin('at.language', 'l')
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
        return $this->createQueryBuilder('at')
            ->select('COUNT(at.id)')
            ->innerJoin('at.language', 'l')
            ->where('l.code = :languageCode')
            ->andWhere('at.name != :empty')
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
    public function duplicateTranslation(AttributeTranslation $sourceTranslation, Language $targetLanguage): AttributeTranslation
    {
        $newTranslation = new AttributeTranslation();
        $newTranslation->setAttribute($sourceTranslation->getAttribute());
        $newTranslation->setLanguage($targetLanguage);
        $newTranslation->setName($sourceTranslation->getName());
        $newTranslation->setDescription($sourceTranslation->getDescription());
        $newTranslation->setPlaceholder($sourceTranslation->getPlaceholder());

        return $newTranslation;
    }
}