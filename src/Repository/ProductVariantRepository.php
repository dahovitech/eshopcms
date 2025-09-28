<?php

namespace App\Repository;

use App\Entity\ProductVariant;
use App\Entity\Product;
use App\Entity\AttributeValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductVariant>
 */
class ProductVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariant::class);
    }

    public function findActiveVariants(): array
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pv.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByProduct(Product $product): array
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.product = :product')
            ->andWhere('pv.isActive = :active')
            ->setParameter('product', $product)
            ->setParameter('active', true)
            ->orderBy('pv.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySku(string $sku): ?ProductVariant
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.sku = :sku')
            ->setParameter('sku', $sku)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByAttributeValues(Product $product, array $attributeValues): ?ProductVariant
    {
        $qb = $this->createQueryBuilder('pv')
            ->where('pv.product = :product')
            ->andWhere('pv.isActive = :active')
            ->setParameter('product', $product)
            ->setParameter('active', true);

        foreach ($attributeValues as $index => $attributeValue) {
            $alias = 'av' . $index;
            $qb->innerJoin('pv.attributeValues', $alias)
                ->andWhere($alias . '.id = :attributeValue' . $index)
                ->setParameter('attributeValue' . $index, $attributeValue->getId());
        }

        // Ensure we have the exact number of attribute values
        $qb->groupBy('pv.id')
            ->having('COUNT(DISTINCT pv.id) = 1');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findWithTranslations(string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('pv')
            ->leftJoin('pv.translations', 't')
            ->addSelect('t')
            ->leftJoin('t.language', 'l')
            ->addSelect('l')
            ->leftJoin('pv.product', 'p')
            ->addSelect('p')
            ->where('pv.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.updatedAt', 'DESC')
            ->addOrderBy('pv.sortOrder', 'ASC');

        if ($languageCode) {
            $qb->andWhere('l.code = :languageCode OR t.id IS NULL')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithAttributeValues(): array
    {
        return $this->createQueryBuilder('pv')
            ->leftJoin('pv.attributeValues', 'av')
            ->addSelect('av')
            ->leftJoin('av.attribute', 'a')
            ->addSelect('a')
            ->leftJoin('pv.product', 'p')
            ->addSelect('p')
            ->where('pv.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.updatedAt', 'DESC')
            ->addOrderBy('pv.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLowStockVariants(): array
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.trackStock = :track')
            ->andWhere('pv.stock <= pv.lowStockThreshold')
            ->andWhere('pv.lowStockThreshold IS NOT NULL')
            ->andWhere('pv.isActive = :active')
            ->setParameter('track', true)
            ->setParameter('active', true)
            ->orderBy('pv.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOutOfStockVariants(): array
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.trackStock = :track')
            ->andWhere('pv.stock = :stock')
            ->andWhere('pv.isActive = :active')
            ->setParameter('track', true)
            ->setParameter('stock', 0)
            ->setParameter('active', true)
            ->orderBy('pv.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByPriceRange(string $minPrice = null, string $maxPrice = null): array
    {
        $qb = $this->createQueryBuilder('pv')
            ->where('pv.isActive = :active')
            ->setParameter('active', true);

        if ($minPrice !== null) {
            $qb->andWhere('(pv.price IS NOT NULL AND pv.price >= :minPrice) OR (pv.price IS NULL AND pv.product.price >= :minPrice)')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb->andWhere('(pv.price IS NOT NULL AND pv.price <= :maxPrice) OR (pv.price IS NULL AND pv.product.price <= :maxPrice)')
                ->setParameter('maxPrice', $maxPrice);
        }

        return $qb->orderBy('COALESCE(pv.price, pv.product.price)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findVariantsByAttributeValue(AttributeValue $attributeValue): array
    {
        return $this->createQueryBuilder('pv')
            ->innerJoin('pv.attributeValues', 'av')
            ->where('av = :attributeValue')
            ->andWhere('pv.isActive = :active')
            ->setParameter('attributeValue', $attributeValue)
            ->setParameter('active', true)
            ->orderBy('pv.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByProduct(Product $product): int
    {
        return $this->createQueryBuilder('pv')
            ->select('COUNT(pv.id)')
            ->where('pv.product = :product')
            ->andWhere('pv.isActive = :active')
            ->setParameter('product', $product)
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveVariants(): int
    {
        return $this->createQueryBuilder('pv')
            ->select('COUNT(pv.id)')
            ->where('pv.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get available attribute values for a product's variants
     */
    public function getAvailableAttributeValuesForProduct(Product $product): array
    {
        $variants = $this->findByProduct($product);
        $attributeValuesMap = [];

        foreach ($variants as $variant) {
            foreach ($variant->getAttributeValues() as $attributeValue) {
                $attributeCode = $attributeValue->getAttribute()->getCode();
                if (!isset($attributeValuesMap[$attributeCode])) {
                    $attributeValuesMap[$attributeCode] = [];
                }
                
                $attributeValuesMap[$attributeCode][$attributeValue->getId()] = $attributeValue;
            }
        }

        return $attributeValuesMap;
    }
}