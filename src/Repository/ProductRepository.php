<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findActiveProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->andWhere('p.publishedAt IS NOT NULL')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->andWhere('p.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.sku = :sku')
            ->setParameter('sku', $sku)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categories', 'c')
            ->where('c = :category')
            ->andWhere('p.status = :status')
            ->setParameter('category', $category)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByBrand(Brand $brand): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.brand = :brand')
            ->andWhere('p.status = :status')
            ->setParameter('brand', $brand)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findVariableProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isVariable = :variable')
            ->andWhere('p.status = :status')
            ->setParameter('variable', true)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWithTranslations(string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')
            ->addSelect('t')
            ->leftJoin('t.language', 'l')
            ->addSelect('l')
            ->where('p.status = :status')
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.updatedAt', 'DESC');

        if ($languageCode) {
            $qb->andWhere('l.code = :languageCode OR t.id IS NULL')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithMedia(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.media', 'm')
            ->addSelect('m')
            ->where('p.status = :status')
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLowStockProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.trackStock = :track')
            ->andWhere('p.stock <= p.lowStockThreshold')
            ->andWhere('p.lowStockThreshold IS NOT NULL')
            ->andWhere('p.status = :status')
            ->setParameter('track', true)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOutOfStockProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.trackStock = :track')
            ->andWhere('p.stock = :stock')
            ->andWhere('p.status = :status')
            ->setParameter('track', true)
            ->setParameter('stock', 0)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentProducts(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->andWhere('p.publishedAt IS NOT NULL')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByPriceRange(string $minPrice = null, string $maxPrice = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::STATUS_ACTIVE);

        if ($minPrice !== null) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        return $qb->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveProducts(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.status = :status')
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByCategory(Category $category): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->innerJoin('p.categories', 'c')
            ->where('c = :category')
            ->andWhere('p.status = :status')
            ->setParameter('category', $category)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByBrand(Brand $brand): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.brand = :brand')
            ->andWhere('p.status = :status')
            ->setParameter('brand', $brand)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Search products by multiple criteria
     */
    public function searchProducts(array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::STATUS_ACTIVE);

        if (!empty($criteria['search'])) {
            $qb->leftJoin('p.translations', 't')
                ->andWhere('t.name LIKE :search OR t.description LIKE :search OR p.sku LIKE :search')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['category'])) {
            $qb->innerJoin('p.categories', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $criteria['category']);
        }

        if (!empty($criteria['brand'])) {
            $qb->andWhere('p.brand = :brandId')
                ->setParameter('brandId', $criteria['brand']);
        }

        if (!empty($criteria['min_price'])) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $criteria['min_price']);
        }

        if (!empty($criteria['max_price'])) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $criteria['max_price']);
        }

        $orderBy = $criteria['sort'] ?? 'updated';
        $order = $criteria['order'] ?? 'DESC';

        switch ($orderBy) {
            case 'price':
                $qb->orderBy('p.price', $order);
                break;
            case 'name':
                $qb->leftJoin('p.translations', 'st')
                    ->orderBy('st.name', $order);
                break;
            case 'created':
                $qb->orderBy('p.createdAt', $order);
                break;
            default:
                $qb->orderBy('p.updatedAt', $order);
        }

        return $qb->getQuery()->getResult();
    }
}