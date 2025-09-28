<?php

namespace App\Repository;

use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brand>
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function findActiveBrands(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.sortOrder', 'ASC')
            ->addOrderBy('b.slug', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Brand
    {
        return $this->createQueryBuilder('b')
            ->where('b.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveBySlug(string $slug): ?Brand
    {
        return $this->createQueryBuilder('b')
            ->where('b.slug = :slug')
            ->andWhere('b.isActive = :active')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithTranslations(string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.translations', 't')
            ->addSelect('t')
            ->leftJoin('t.language', 'l')
            ->addSelect('l')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.sortOrder', 'ASC');

        if ($languageCode) {
            $qb->andWhere('l.code = :languageCode OR t.id IS NULL')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllOrderedBySortOrder(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.sortOrder', 'ASC')
            ->addOrderBy('b.slug', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveBrands(): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}