<?php

namespace App\Controller\Admin;

use App\Entity\ProductVariant;
use App\Entity\Product;
use App\Repository\ProductVariantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/product-variant', name: 'admin_product_variant_')]
#[IsGranted('ROLE_ADMIN')]
class ProductVariantController extends AbstractController
{
    public function __construct(
        private ProductVariantRepository $productVariantRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['productId']) || !isset($data['sku'])) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }
        
        $product = $this->entityManager->getRepository(Product::class)->find($data['productId']);
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }
        
        $variant = new ProductVariant();
        $variant->setProduct($product);
        $variant->setSku($data['sku']);
        
        if (isset($data['price'])) {
            $variant->setPrice($data['price']);
        }
        if (isset($data['stock'])) {
            $variant->setStock((int)$data['stock']);
        }
        if (isset($data['weight'])) {
            $variant->setWeight($data['weight']);
        }
        if (isset($data['sortOrder'])) {
            $variant->setSortOrder((int)$data['sortOrder']);
        }
        
        $variant->setIsActive($data['isActive'] ?? true);
        $variant->setTrackStock($data['trackStock'] ?? true);
        
        $this->entityManager->persist($variant);
        $this->entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'variant' => [
                'id' => $variant->getId(),
                'sku' => $variant->getSku(),
                'price' => $variant->getPrice(),
                'stock' => $variant->getStock(),
                'isActive' => $variant->isActive()
            ]
        ]);
    }
    
    #[Route('/{id}/update', name: 'update', methods: ['POST'])]
    public function update(Request $request, ProductVariant $variant): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }
        
        if (isset($data['sku'])) {
            $variant->setSku($data['sku']);
        }
        if (isset($data['price'])) {
            $variant->setPrice($data['price'] ?: null);
        }
        if (isset($data['compareAtPrice'])) {
            $variant->setCompareAtPrice($data['compareAtPrice'] ?: null);
        }
        if (isset($data['costPrice'])) {
            $variant->setCostPrice($data['costPrice'] ?: null);
        }
        if (isset($data['stock'])) {
            $variant->setStock((int)$data['stock']);
        }
        if (isset($data['lowStockThreshold'])) {
            $variant->setLowStockThreshold($data['lowStockThreshold'] ? (int)$data['lowStockThreshold'] : null);
        }
        if (isset($data['weight'])) {
            $variant->setWeight($data['weight'] ?: null);
        }
        if (isset($data['sortOrder'])) {
            $variant->setSortOrder((int)$data['sortOrder']);
        }
        
        if (isset($data['isActive'])) {
            $variant->setIsActive($data['isActive']);
        }
        if (isset($data['trackStock'])) {
            $variant->setTrackStock($data['trackStock']);
        }
        
        $this->entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'variant' => [
                'id' => $variant->getId(),
                'sku' => $variant->getSku(),
                'price' => $variant->getPrice(),
                'stock' => $variant->getStock(),
                'isActive' => $variant->isActive()
            ]
        ]);
    }
    
    #[Route('/{id}/delete', name: 'delete', methods: ['DELETE'])]
    public function delete(ProductVariant $variant): JsonResponse
    {
        $this->entityManager->remove($variant);
        $this->entityManager->flush();
        
        return new JsonResponse(['success' => true]);
    }
    
    #[Route('/product/{productId}', name: 'list', methods: ['GET'])]
    public function listByProduct(int $productId): JsonResponse
    {
        $product = $this->entityManager->getRepository(Product::class)->find($productId);
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }
        
        $variants = $this->productVariantRepository->findBy(['product' => $product], ['sortOrder' => 'ASC']);
        
        $variantsData = [];
        foreach ($variants as $variant) {
            $variantsData[] = [
                'id' => $variant->getId(),
                'sku' => $variant->getSku(),
                'price' => $variant->getPrice(),
                'compareAtPrice' => $variant->getCompareAtPrice(),
                'costPrice' => $variant->getCostPrice(),
                'stock' => $variant->getStock(),
                'lowStockThreshold' => $variant->getLowStockThreshold(),
                'weight' => $variant->getWeight(),
                'sortOrder' => $variant->getSortOrder(),
                'isActive' => $variant->isActive(),
                'trackStock' => $variant->isTrackStock()
            ];
        }
        
        return new JsonResponse(['variants' => $variantsData]);
    }
}