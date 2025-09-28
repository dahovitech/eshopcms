<?php

namespace App\Repository;

use App\Entity\AttributeValueTranslation;
use App\Entity\Language;
use App\Entity\AttributeValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AttributeValueTranslation>
 */
class AttributeValueTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeValueTranslation::class);
    }

    /**
     * Find translation by attribute value and language
     */
    public function findByAttributeValueAndLanguage(AttributeValue $attributeValue, Language $language): ?AttributeValueTranslation
    {
        return $this->createQueryBuilder('avt')
            ->where('avt.attributeValue = :attributeValue')
            ->andWhere('avt.language = :language')
            ->setParameter('attributeValue', $attributeValue)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find translations by language code
     */
    public function findByLanguageCode(string $languageCode): array
    {
        return $this->createQueryBuilder('avt')
            ->innerJoin('avt.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->orderBy('avt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find incomplete translations (missing name)
     */
    public function findIncompleteTranslations(?string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('avt')
            ->where('avt.name = :empty')
            ->setParameter('empty', '');

        if ($languageCode) {
            $qb->innerJoin('avt.language', 'l')
                ->andWhere('l.code = :languageCode')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->orderBy('avt.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count translations by language
     */
    public function countByLanguage(string $languageCode): int
    {
        return $this->createQueryBuilder('avt')
            ->select('COUNT(avt.id)')
            ->innerJoin('avt.language', 'l')
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
        return $this->createQueryBuilder('avt')
            ->select('COUNT(avt.id)')
            ->innerJoin('avt.language', 'l')
            ->where('l.code = :languageCode')
            ->andWhere('avt.name != :empty')
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
    public function duplicateTranslation(AttributeValueTranslation $sourceTranslation, Language $targetLanguage): AttributeValueTranslation
    {
        $newTranslation = new AttributeValueTranslation();
        $newTranslation->setAttributeValue($sourceTranslation->getAttributeValue());
        $newTranslation->setLanguage($targetLanguage);
        $newTranslation->setName($sourceTranslation->getName());
        $newTranslation->setDescription($sourceTranslation->getDescription());

        return $newTranslation;
    }
}