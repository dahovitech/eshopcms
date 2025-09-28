<?php

namespace App\Repository;

use App\Entity\Attribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Attribute>
 */
class AttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribute::class);
    }

    public function findActiveAttributes(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.sortOrder', 'ASC')
            ->addOrderBy('a.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?Attribute
    {
        return $this->createQueryBuilder('a')
            ->where('a.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveByCode(string $code): ?Attribute
    {
        return $this->createQueryBuilder('a')
            ->where('a.code = :code')
            ->andWhere('a.isActive = :active')
            ->setParameter('code', $code)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findVariantAttributes(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isVariant = :variant')
            ->andWhere('a.isActive = :active')
            ->setParameter('variant', true)
            ->setParameter('active', true)
            ->orderBy('a.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFilterableAttributes(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isFilterable = :filterable')
            ->andWhere('a.isActive = :active')
            ->setParameter('filterable', true)
            ->setParameter('active', true)
            ->orderBy('a.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type = :type')
            ->andWhere('a.isActive = :active')
            ->setParameter('type', $type)
            ->setParameter('active', true)
            ->orderBy('a.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithTranslations(string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 't')
            ->addSelect('t')
            ->leftJoin('t.language', 'l')
            ->addSelect('l')
            ->where('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.sortOrder', 'ASC');

        if ($languageCode) {
            $qb->andWhere('l.code = :languageCode OR t.id IS NULL')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithValues(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.values', 'v')
            ->addSelect('v')
            ->where('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.sortOrder', 'ASC')
            ->addOrderBy('v.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedBySortOrder(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.sortOrder', 'ASC')
            ->addOrderBy('a.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveAttributes(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}