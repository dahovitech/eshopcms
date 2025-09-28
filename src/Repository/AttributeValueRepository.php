<?php

namespace App\Repository;

use App\Entity\AttributeValue;
use App\Entity\Attribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AttributeValue>
 */
class AttributeValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeValue::class);
    }

    public function findActiveValues(): array
    {
        return $this->createQueryBuilder('av')
            ->where('av.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('av.sortOrder', 'ASC')
            ->addOrderBy('av.value', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAttribute(Attribute $attribute): array
    {
        return $this->createQueryBuilder('av')
            ->where('av.attribute = :attribute')
            ->andWhere('av.isActive = :active')
            ->setParameter('attribute', $attribute)
            ->setParameter('active', true)
            ->orderBy('av.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAttributeCode(string $attributeCode): array
    {
        return $this->createQueryBuilder('av')
            ->innerJoin('av.attribute', 'a')
            ->where('a.code = :code')
            ->andWhere('av.isActive = :active')
            ->andWhere('a.isActive = :active')
            ->setParameter('code', $attributeCode)
            ->setParameter('active', true)
            ->orderBy('av.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByValue(string $value): array
    {
        return $this->createQueryBuilder('av')
            ->where('av.value = :value')
            ->andWhere('av.isActive = :active')
            ->setParameter('value', $value)
            ->setParameter('active', true)
            ->orderBy('av.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findColorValues(): array
    {
        return $this->createQueryBuilder('av')
            ->innerJoin('av.attribute', 'a')
            ->where('a.type = :type')
            ->andWhere('av.isActive = :active')
            ->andWhere('a.isActive = :active')
            ->setParameter('type', 'color')
            ->setParameter('active', true)
            ->orderBy('av.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithTranslations(string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('av')
            ->leftJoin('av.translations', 't')
            ->addSelect('t')
            ->leftJoin('t.language', 'l')
            ->addSelect('l')
            ->leftJoin('av.attribute', 'a')
            ->addSelect('a')
            ->where('av.isActive = :active')
            ->andWhere('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.sortOrder', 'ASC')
            ->addOrderBy('av.sortOrder', 'ASC');

        if ($languageCode) {
            $qb->andWhere('l.code = :languageCode OR t.id IS NULL')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->getQuery()->getResult();
    }

    public function findUsedInVariants(): array
    {
        return $this->createQueryBuilder('av')
            ->innerJoin('av.productVariants', 'pv')
            ->where('av.isActive = :active')
            ->setParameter('active', true)
            ->groupBy('av.id')
            ->orderBy('av.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedBySortOrder(): array
    {
        return $this->createQueryBuilder('av')
            ->leftJoin('av.attribute', 'a')
            ->addSelect('a')
            ->orderBy('a.sortOrder', 'ASC')
            ->addOrderBy('av.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByAttribute(Attribute $attribute): int
    {
        return $this->createQueryBuilder('av')
            ->select('COUNT(av.id)')
            ->where('av.attribute = :attribute')
            ->andWhere('av.isActive = :active')
            ->setParameter('attribute', $attribute)
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveValues(): int
    {
        return $this->createQueryBuilder('av')
            ->select('COUNT(av.id)')
            ->where('av.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}