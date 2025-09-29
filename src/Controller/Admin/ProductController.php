<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\ProductVariant;
use App\Entity\Language;
use App\Form\ProductType;
use App\Repository\LanguageRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTranslationRepository;
use App\Repository\CategoryRepository;
use App\Repository\BrandRepository;
use App\Repository\MediaRepository;
use App\Repository\AttributeRepository;
use App\Service\MediaService;
use App\Service\SlugService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/product', name: 'admin_product_')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private LanguageRepository $languageRepository,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private ProductTranslationRepository $productTranslationRepository,
        private MediaRepository $mediaRepository,
        private AttributeRepository $attributeRepository,
        private MediaService $mediaService,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Calcul des statistiques simples
        $statistics = [
            'totalProducts' => count($products),
            'activeProducts' => count(array_filter($products, fn($p) => $p->isActive())),
            'lowStockProducts' => count(array_filter($products, fn($p) => $p->getStock() !== null && $p->getStock() <= 5))
        ];

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
            'languages' => $languages,
            'statistics' => $statistics
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Initialize translations for all active languages
        foreach ($languages as $language) {
            $translation = new ProductTranslation();
            $translation->setLanguage($language);
            $product->addTranslation($translation);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Generate slugs for translations
                foreach ($product->getTranslations() as $translation) {
                    if ($translation->getName() && !$translation->getSlug()) {
                        $slug = $this->slugger->slug($translation->getName())->lower();
                        $translation->setSlug($slug);
                    }
                }

                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $this->addFlash('success', 'Produit créé avec succès.');
                return $this->redirectToRoute('admin_product_show', ['id' => $product->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'languages' => $languages,
            'attributes' => $this->attributeRepository->findBy(['isActive' => true], ['sortOrder' => 'ASC'])
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $translations = [];
        
        foreach ($languages as $language) {
            $translation = $this->productTranslationRepository->findOneBy([
                'product' => $product,
                'language' => $language
            ]);
            if ($translation) {
                $translations[$language->getCode()] = $translation;
            }
        }

        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
            'translations' => $translations,
            'languages' => $languages
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Product $product): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Ensure translations exist for all active languages
        foreach ($languages as $language) {
            if (!$product->hasTranslation($language->getCode())) {
                $translation = new ProductTranslation();
                $translation->setLanguage($language);
                $product->addTranslation($translation);
            }
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Update slugs for translations
                foreach ($product->getTranslations() as $translation) {
                    if ($translation->getName() && !$translation->getSlug()) {
                        $slug = $this->slugger->slug($translation->getName())->lower();
                        $translation->setSlug($slug);
                    }
                }

                $this->entityManager->flush();

                $this->addFlash('success', 'Produit modifié avec succès.');
                return $this->redirectToRoute('admin_product_show', ['id' => $product->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'languages' => $languages,
            'attributes' => $this->attributeRepository->findBy(['isActive' => true], ['sortOrder' => 'ASC'])
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleStatus(Product $product): JsonResponse
    {
        $currentStatus = $product->getStatus();
        $newStatus = $currentStatus === Product::STATUS_ACTIVE ? Product::STATUS_INACTIVE : Product::STATUS_ACTIVE;
        
        $product->setStatus($newStatus);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'status' => $newStatus,
            'isActive' => $newStatus === Product::STATUS_ACTIVE
        ]);
    }

    #[Route('/{id}/variants', name: 'variants', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function variants(Product $product): Response
    {
        return $this->render('admin/product/variants.html.twig', [
            'product' => $product,
            'attributes' => $this->attributeRepository->findBy([
                'isVariant' => true, 
                'isActive' => true
            ], ['sortOrder' => 'ASC'])
        ]);
    }

    #[Route('/{id}/add-variant', name: 'add_variant', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addVariant(Request $request, Product $product): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $variant = new ProductVariant();
            $variant->setProduct($product);
            $variant->setSku($data['sku'] ?? '');
            $variant->setPrice($data['price'] ?? null);
            $variant->setStock($data['stock'] ?? 0);
            
            // Handle attribute values
            if (isset($data['attributeValues']) && is_array($data['attributeValues'])) {
                foreach ($data['attributeValues'] as $attributeValueId) {
                    $attributeValue = $this->entityManager->getRepository('App:AttributeValue')->find($attributeValueId);
                    if ($attributeValue) {
                        $variant->addAttributeValue($attributeValue);
                    }
                }
            }
            
            $this->entityManager->persist($variant);
            $this->entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'variant' => [
                    'id' => $variant->getId(),
                    'sku' => $variant->getSku(),
                    'price' => $variant->getPrice(),
                    'stock' => $variant->getStock()
                ]
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/media-library', name: 'media_library', methods: ['GET'])]
    public function mediaLibrary(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('search');
        
        $result = $this->mediaService->getMediaList($page, 20, $search);
        
        $mediaData = [];
        foreach ($result['medias'] as $media) {
            $mediaData[] = [
                'id' => $media->getId(),
                'fileName' => $media->getFileName(),
                'alt' => $media->getAlt(),
                'webPath' => $media->getWebPath(),
                'extension' => $media->getExtension()
            ];
        }
        
        return new JsonResponse([
            'medias' => $mediaData,
            'total' => $result['total'],
            'totalPages' => $result['totalPages'],
            'currentPage' => $result['currentPage']
        ]);
    }

}