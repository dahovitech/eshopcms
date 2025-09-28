<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findActiveCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.level', 'ASC')
            ->addOrderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRootCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.parent IS NULL')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveBySlug(string $slug): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->andWhere('c.isActive = :active')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithTranslations(string $languageCode = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.translations', 't')
            ->addSelect('t')
            ->leftJoin('t.language', 'l')
            ->addSelect('l')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.level', 'ASC')
            ->addOrderBy('c.sortOrder', 'ASC');

        if ($languageCode) {
            $qb->andWhere('l.code = :languageCode OR t.id IS NULL')
                ->setParameter('languageCode', $languageCode);
        }

        return $qb->getQuery()->getResult();
    }

    public function findCategoryTree(string $languageCode = null): array
    {
        $categories = $this->findWithTranslations($languageCode);
        return $this->buildTree($categories);
    }

    private function buildTree(array $categories, ?Category $parent = null): array
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category->getParent() === $parent) {
                $categoryData = [
                    'category' => $category,
                    'children' => $this->buildTree($categories, $category)
                ];
                $tree[] = $categoryData;
            }
        }
        
        return $tree;
    }

    public function findByLevel(int $level): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.level = :level')
            ->andWhere('c.isActive = :active')
            ->setParameter('level', $level)
            ->setParameter('active', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findChildren(Category $parent): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.parent = :parent')
            ->andWhere('c.isActive = :active')
            ->setParameter('parent', $parent)
            ->setParameter('active', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedBySortOrder(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.level', 'ASC')
            ->addOrderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveCategories(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countProductsByCategory(Category $category): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(p.id)')
            ->leftJoin('c.products', 'p')
            ->where('c = :category')
            ->andWhere('p.status = :status')
            ->setParameter('category', $category)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }
}